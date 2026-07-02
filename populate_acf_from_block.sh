#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./populate_acf_from_block.sh blocks/lc-example.php
#
# Optional per-template schema manifest in the PHP file:
#
#   /*
#   ACF_FIELDS_START
#   # field_name|field_type|values|default_value|wrapper_width|instructions
#   order|radio|Text Pullout,Pullout Text|Text Pullout|33|Layout order
#   flourish|true_false||0|33|Enable flourish striping
#   ACF_FIELDS_END
#   */
#
# Manifest entries override inferred field types/options for matching names.

if ! command -v python3 >/dev/null 2>&1; then
  echo "python3 is required." >&2
  exit 1
fi

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <block-template-php>" >&2
  echo "Example: $0 blocks/lc-cta.php" >&2
  exit 1
fi

BLOCK_TEMPLATE="$1"

python3 - "$BLOCK_TEMPLATE" <<'PY'
import json
import os
import re
import sys
import hashlib
import copy
from datetime import datetime

block_template = os.path.abspath(sys.argv[1])

if not os.path.isfile(block_template):
    print(f"Block template not found: {block_template}", file=sys.stderr)
    sys.exit(1)

block_dir = os.path.dirname(block_template)
if os.path.basename(block_dir) == "blocks":
    base_dir = os.path.dirname(block_dir)
else:
    base_dir = os.getcwd()

acf_dir = os.path.join(base_dir, "acf-json")
os.makedirs(acf_dir, exist_ok=True)

block_file = os.path.basename(block_template)
block_kebab, ext = os.path.splitext(block_file)
if ext.lower() != ".php":
    print(f"Expected a .php block template, got: {block_template}", file=sys.stderr)
    sys.exit(1)

block_slug = block_kebab.replace("-", "_")
json_path = os.path.join(acf_dir, f"group_{block_slug}.json")

def titleize(s: str) -> str:
    return " ".join(w.capitalize() for w in re.split(r"[_\-\s]+", s.strip()) if w)

def labelize_field_name(name: str) -> str:
    cleaned = re.sub(r"^lc[_\-]+", "", name.strip(), flags=re.IGNORECASE)
    return titleize(cleaned)

def strip_php_comments(source: str) -> str:
    # Remove comments to avoid picking up field calls inside docs/commented code.
    return re.sub(r"/\*.*?\*/|//.*?$|#.*?$", "", source, flags=re.DOTALL | re.MULTILINE)

def parse_template_manifest(source: str) -> dict:
    # Parse optional line-based manifest between ACF_FIELDS_START / ACF_FIELDS_END markers.
    m = re.search(r"ACF_FIELDS_START(.*?)ACF_FIELDS_END", source, flags=re.DOTALL)
    if not m:
        return {}

    rules = {}
    body = m.group(1)
    raw_lines = body.splitlines()

    for idx, raw_line in enumerate(raw_lines, start=1):
        line = raw_line.strip()
        if line.startswith("*"):
            line = line.lstrip("*").strip()
        if not line or line.startswith("//") or line.startswith("#"):
            continue

        parts = [p.strip() for p in line.split("|")]
        if len(parts) < 2:
            print(
                f"Invalid manifest entry in {block_template} line {idx}: '{raw_line.strip()}'",
                file=sys.stderr,
            )
            sys.exit(1)

        name = parts[0].lower()
        ftype = parts[1]
        values = parts[2] if len(parts) > 2 else ""
        default = parts[3] if len(parts) > 3 else ""
        wrapper_width = parts[4] if len(parts) > 4 else ""
        instructions = "|".join(parts[5:]).strip() if len(parts) > 5 else ""

        if not name:
            print(f"Empty field name in manifest in {block_template} line {idx}", file=sys.stderr)
            sys.exit(1)

        rule = {"type": ftype}
        if ftype in ("radio", "select"):
            choices = [v.strip() for v in values.split(",") if v.strip()]
            if not choices:
                print(
                    f"Field '{name}' with type '{ftype}' requires comma-separated values in manifest ({block_template} line {idx})",
                    file=sys.stderr,
                )
                sys.exit(1)
            rule["choices"] = {c: c for c in choices}
            rule["default_value"] = default if default else choices[0]
            rule["return_format"] = "value"
            rule["allow_null"] = 0
            if ftype == "radio":
                rule["other_choice"] = 0
                rule["layout"] = "vertical"
                rule["save_other_choice"] = 0
            else:
                rule["multiple"] = 0
                rule["ui"] = 0
                rule["ajax"] = 0
                rule["placeholder"] = ""
        elif ftype == "true_false":
            rule["default_value"] = 1 if default.strip() in ("1", "true", "yes", "on") else 0
            rule["ui"] = 1
            rule["ui_on_text"] = ""
            rule["ui_off_text"] = ""
        elif default:
            rule["default_value"] = default

        if wrapper_width:
            rule["wrapper"] = {"width": wrapper_width, "class": "", "id": ""}

        if instructions:
            rule["instructions"] = instructions

        rules[name] = rule

    return rules

def expected_type(name: str, rules: dict) -> str:
    rule = rules.get(name.lower())
    if isinstance(rule, dict) and rule.get("type"):
        return rule["type"]
    return infer_type(name)

def infer_type(name: str) -> str:
    n = name.lower()
    if n.startswith(("is_", "has_", "show_", "enable_", "disable_")):
        return "true_false"
    if n.endswith(("_enabled", "_disabled", "_active", "_visible", "_hidden")):
        return "true_false"
    if any(k in n for k in ["image", "icon", "logo", "photo", "thumbnail"]):
        return "image"
    if any(k in n for k in ["link"]):
        return "link"
    if any(k in n for k in ["url", "href"]):
        return "url"
    if any(k in n for k in ["content", "description", "excerpt", "copy", "text", "body", "message"]):
        return "textarea"
    return "text"

def safe_key(prefix: str, name: str) -> str:
    n = re.sub(r"[^a-z0-9_]+", "_", name.lower()).strip("_")
    if not n:
        n = "field"
    digest = hashlib.sha1(f"{prefix}:{name}".encode("utf-8")).hexdigest()[:8]
    return f"field_{prefix}_{n}_{digest}"

def field_template(prefix: str, name: str, ftype: str, rules: dict):
    field = {
        "key": safe_key(prefix, name),
        "label": labelize_field_name(name),
        "name": name,
        "type": ftype,
        "instructions": "",
        "required": 0,
        "conditional_logic": 0,
        "wrapper": {
            "width": "",
            "class": "",
            "id": ""
        }
    }

    if ftype in ("text", "url"):
        field.update({
            "default_value": "",
            "placeholder": "",
            "prepend": "",
            "append": ""
        })
    elif ftype == "textarea":
        field.update({
            "default_value": "",
            "placeholder": "",
            "rows": 4,
            "new_lines": "wpautop"
        })
    elif ftype == "wysiwyg":
        field.update({
            "default_value": "",
            "tabs": "all",
            "toolbar": "full",
            "media_upload": 1,
            "delay": 1
        })
    elif ftype == "link":
        field.update({
            "return_format": "array"
        })
    elif ftype == "image":
        field.update({
            "return_format": "id",
            "preview_size": "thumbnail",
            "library": "all"
        })
    elif ftype == "true_false":
        field.update({
            "default_value": 0,
            "ui": 1,
            "ui_on_text": "",
            "ui_off_text": ""
        })

    rule = rules.get(name.lower())
    if isinstance(rule, dict):
        field["type"] = rule.get("type", field["type"])
        for k, v in rule.items():
            if k == "type":
                continue
            field[k] = copy.deepcopy(v)

    return field

def repeater_template(prefix: str, name: str):
    return {
        "key": safe_key(prefix, name),
        "label": labelize_field_name(name),
        "name": name,
        "type": "repeater",
        "instructions": "",
        "required": 0,
        "conditional_logic": 0,
        "wrapper": {
            "width": "",
            "class": "",
            "id": ""
        },
        "layout": "block",
        "button_label": "Add Row",
        "sub_fields": []
    }

with open(block_template, "r", encoding="utf-8") as f:
    php_raw = f.read()

template_field_rules = parse_template_manifest(php_raw)
field_rules = copy.deepcopy(template_field_rules)
php = strip_php_comments(php_raw)

fn_re = re.compile(
    r"\b(?P<fn>get_field|the_field|have_rows|get_sub_field|the_sub_field)\s*\(\s*['\"](?P<name>[a-zA-Z0-9_\-]+)['\"]",
    re.MULTILINE,
)

seen_order = []
for m in fn_re.finditer(php):
    seen_order.append((m.group("fn"), m.group("name")))

top_fields = []
repeaters = []
sub_fields = []

def push_unique(arr, item):
    if item not in arr:
        arr.append(item)

for fn, name in seen_order:
    if fn in ("get_field", "the_field"):
        push_unique(top_fields, name)
    elif fn == "have_rows":
        push_unique(repeaters, name)
    elif fn in ("get_sub_field", "the_sub_field"):
        push_unique(sub_fields, name)

if not top_fields and not repeaters and not sub_fields:
    print("No ACF field calls found in block template. Nothing to update.")
    sys.exit(0)

if os.path.isfile(json_path):
    try:
        with open(json_path, "r", encoding="utf-8") as f:
            data = json.load(f)
    except json.JSONDecodeError as exc:
        print(f"Invalid JSON in existing file: {json_path}: {exc}", file=sys.stderr)
        sys.exit(1)
else:
    data = {
        "key": f"group_{block_slug}",
        "title": titleize(block_slug),
        "fields": [],
        "location": [[{
            "param": "block",
            "operator": "==",
            "value": f"acf/{block_kebab}"
        }]],
        "menu_order": 0,
        "position": "normal",
        "style": "default",
        "label_placement": "top",
        "instruction_placement": "label",
        "hide_on_screen": "",
        "active": True,
        "description": "",
        "show_in_rest": 0
    }

original_data = copy.deepcopy(data)

if "fields" not in data or not isinstance(data["fields"], list):
    data["fields"] = []

fields = data["fields"]

def normalize_labels(field_list):
    for fld in field_list:
        if not isinstance(fld, dict):
            continue
        if fld.get("type") == "message":
            # Message fields are intro/notes only; keep them nameless so they
            # never collide with real data field names.
            if fld.get("name"):
                fld["name"] = ""
            sub = fld.get("sub_fields")
            if isinstance(sub, list):
                normalize_labels(sub)
            continue
        nm = fld.get("name")
        if nm:
            fld["label"] = labelize_field_name(nm)
        sub = fld.get("sub_fields")
        if isinstance(sub, list):
            normalize_labels(sub)

normalize_labels(fields)

existing = {}
for idx, fld in enumerate(fields):
    nm = fld.get("name")
    if nm:
        existing[nm] = idx

added = []
updated = []

def can_auto_upgrade_field(field: dict, prefix: str) -> bool:
    key = field.get("key", "")
    # Only rewrite fields this generator likely created.
    return isinstance(key, str) and key.startswith(f"field_{prefix}_")

def sync_field_with_rule(prefix: str, field: dict, name: str) -> bool:
    rule = field_rules.get(name.lower())
    if not isinstance(rule, dict):
        return False
    if not can_auto_upgrade_field(field, prefix):
        return False

    target_type = expected_type(name, field_rules)
    replacement = field_template(prefix, name, target_type, field_rules)

    before = copy.deepcopy(field)

    # Preserve editorial metadata from existing field.
    replacement["key"] = field.get("key", replacement["key"])
    replacement["label"] = field.get("label", replacement["label"])
    replacement["required"] = field.get("required", 0)
    replacement["conditional_logic"] = field.get("conditional_logic", 0)

    # Keep existing wrapper/instructions unless explicitly set by manifest rule.
    if "wrapper" not in rule:
        replacement["wrapper"] = field.get("wrapper", {"width": "", "class": "", "id": ""})
    if "instructions" not in rule:
        replacement["instructions"] = field.get("instructions", "")

    field.clear()
    field.update(replacement)
    return before != field

for name in top_fields:
    if name not in existing:
        continue
    idx = existing[name]
    if sync_field_with_rule(block_slug, fields[idx], name):
        updated.append(name)

for name in top_fields:
    if name in existing:
        continue
    ftype = expected_type(name, field_rules)
    fields.append(field_template(block_slug, name, ftype, field_rules))
    existing[name] = len(fields) - 1
    added.append(name)

for rep in repeaters:
    if rep not in existing:
        fields.append(repeater_template(block_slug, rep))
        existing[rep] = len(fields) - 1
        added.append(rep)
    else:
        if fields[existing[rep]].get("type") != "repeater":
            fields[existing[rep]]["type"] = "repeater"
            fields[existing[rep]].setdefault("sub_fields", [])

warnings = []

# Warn when existing top-level field types differ from what this generator
# would infer, to avoid silently changing schema choices.
for name in top_fields:
    if name not in existing:
        continue
    inferred = expected_type(name, field_rules)
    current = fields[existing[name]].get("type")
    if current == "message":
        continue
    if current and current != inferred:
        warnings.append(
            f"Field '{name}' exists as type '{current}' but inferred '{inferred}'. Kept existing type."
        )

if sub_fields:
    if len(repeaters) == 1:
        rep_name = repeaters[0]
        rep_field = fields[existing[rep_name]]
        rep_field.setdefault("sub_fields", [])

        existing_sub = {sf.get("name") for sf in rep_field["sub_fields"] if isinstance(sf, dict)}
        for sf_name in sub_fields:
            if sf_name in existing_sub:
                continue
            sf_type = expected_type(sf_name, field_rules)
            rep_field["sub_fields"].append(field_template(f"{block_slug}_{rep_name}", sf_name, sf_type, field_rules))
            added.append(f"{rep_name}.{sf_name}")
    elif len(repeaters) == 0:
        for sf_name in sub_fields:
            if sf_name in existing:
                continue
            sf_type = expected_type(sf_name, field_rules)
            fields.append(field_template(block_slug, sf_name, sf_type, field_rules))
            existing[sf_name] = len(fields) - 1
            added.append(sf_name)
        warnings.append("Found get_sub_field()/the_sub_field() without have_rows(); added them as top-level fields.")
    else:
        warnings.append("Multiple have_rows() repeaters found; could not reliably map sub fields. Added repeaters only.")

def without_modified(payload: dict) -> dict:
    cloned = copy.deepcopy(payload)
    cloned.pop("modified", None)
    return cloned

changed = without_modified(data) != without_modified(original_data)

if not changed:
    print(f"No changes needed: {json_path}")
    if warnings:
        for w in warnings:
            print(f"Warning: {w}")
    sys.exit(0)

data["modified"] = int(datetime.now().timestamp())

if os.path.isfile(json_path):
    backup = f"{json_path}.bak"
    with open(json_path, "r", encoding="utf-8") as src, open(backup, "w", encoding="utf-8") as dst:
        dst.write(src.read())

with open(json_path, "w", encoding="utf-8") as f:
    json.dump(data, f, indent=2, ensure_ascii=False)
    f.write("\n")

print(f"Updated: {json_path}")
if added:
    print("Added fields:")
    for x in added:
        print(f"  - {x}")
else:
    print("No new fields were added (already up to date).")

if updated:
    print("Updated field schema:")
    for x in updated:
        print(f"  - {x}")

for w in warnings:
    print(f"Warning: {w}")
PY