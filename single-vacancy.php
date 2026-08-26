<?php
/**
 * Single Vacancy template.
 *
 * Vacancies have a fixed structure that HR populate through ACF fields rather
 * than blocks, so they render through this template instead of page.php. That
 * keeps every role consistent and guarantees the JobPosting schema below has
 * the data it needs.
 *
 * The schema block is ported from cb-turnpower2025 (sister company, same
 * field names) so behaviour matches a known-good Google Jobs implementation.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$job_id = get_the_ID();

$role_purpose         = get_field( 'role_purpose', $job_id );
$key_responsibilities = get_field( 'key_responsibilities', $job_id );
$skills_experience    = get_field( 'skills_experience', $job_id );
$benefits             = get_field( 'benefits', $job_id );
$equality_inclusion   = get_field( 'equality_inclusion', $job_id );

$employment_type = get_field( 'employment_type', $job_id );
$tenure          = get_field( 'tenure', $job_id );
$date_posted     = get_field( 'date_posted', $job_id );
$valid_through   = get_field( 'valid_through', $job_id );

$location_type    = get_field( 'location_type', $job_id );
$street_address   = get_field( 'street_address', $job_id );
$address_locality = get_field( 'address_locality', $job_id );
$address_region   = get_field( 'address_region', $job_id );
$postal_code      = get_field( 'postal_code', $job_id );
$address_country  = get_field( 'address_country', $job_id );

$salary_type     = get_field( 'salary_type', $job_id );
$minimum_salary  = get_field( 'minimum_salary', $job_id );
$maximum_salary  = get_field( 'maximum_salary', $job_id );
$salary_currency = get_field( 'salary_currency', $job_id ) ? get_field( 'salary_currency', $job_id ) : 'GBP';
$salary_unit     = get_field( 'salary_unit', $job_id ) ? get_field( 'salary_unit', $job_id ) : 'YEAR';

$form_id  = (int) apply_filters( 'cb_vacancy_application_form_id', 4 );
$hero_id  = get_post_thumbnail_id( $job_id ) ? get_post_thumbnail_id( $job_id ) : 22;
$hero_url = wp_get_attachment_image_url( $hero_id, 'full' );

$emp_labels    = array(
	'FULL_TIME'  => 'Full time',
	'PART_TIME'  => 'Part time',
	'CONTRACTOR' => 'Contractor',
	'TEMPORARY'  => 'Temporary',
	'INTERN'     => 'Intern',
	'OTHER'      => 'Other',
);
$tenure_labels = array(
	'permanent'      => 'Permanent',
	'temporary'      => 'Temporary',
	'contract'       => 'Contract',
	'fixed_term'     => 'Fixed term',
	'apprenticeship' => 'Apprenticeship',
	'zero_hours'     => 'Zero hours',
);
$unit_map      = array(
	'YEAR'  => 'per year',
	'MONTH' => 'per month',
	'WEEK'  => 'per week',
	'DAY'   => 'per day',
	'HOUR'  => 'per hour',
);

if ( 'remote' === $location_type ) {
	$location_label = 'Remote';
} elseif ( 'hybrid' === $location_type ) {
	$location_label = 'Hybrid' . ( $address_locality ? ' / ' . $address_locality : '' );
} else {
	$location_label = $address_locality ? $address_locality : 'On site';
}

if ( 'range' === $salary_type && ( $minimum_salary || $maximum_salary ) ) {
	$unit_str = $unit_map[ $salary_unit ] ?? strtolower( (string) $salary_unit );
	if ( $minimum_salary && $maximum_salary ) {
		$salary_label = '£' . number_format( (int) $minimum_salary ) . ' - £' . number_format( (int) $maximum_salary ) . ' ' . $unit_str;
	} else {
		$salary_label = '£' . number_format( (int) ( $minimum_salary ? $minimum_salary : $maximum_salary ) ) . '+ ' . $unit_str;
	}
} else {
	$salary_label = 'On application';
}

$sections = array(
	'About the role'         => $role_purpose,
	'What you will be doing' => $key_responsibilities,
	'What we are looking for' => $skills_experience,
	'What we offer'          => $benefits,
);
?>
<main id="main" class="cb-vacancy">
	<section class="cb-hero cb-hero--bottom-curve">
		<?php
		if ( $hero_url ) {
			?>
		<div class="cb-hero__bg" style="background-image:url('<?= esc_url( $hero_url ); ?>');"></div>
			<?php
		}
		?>
		<div class="cb-hero__scrim"></div>
		<div class="container">
			<h1><?= esc_html( get_the_title() ); ?></h1>
			<p class="cb-hero__intro">
				<?= esc_html( $location_label ); ?>
				<?php
				if ( ! empty( $tenure_labels[ $tenure ] ) ) {
					?>
				&middot; <?= esc_html( $tenure_labels[ $tenure ] ); ?>
					<?php
				}
				if ( ! empty( $emp_labels[ $employment_type ] ) ) {
					?>
				&middot; <?= esc_html( $emp_labels[ $employment_type ] ); ?>
					<?php
				}
				?>
				&middot; <?= esc_html( $salary_label ); ?>
			</p>
		</div>
	</section>

	<section class="cb-vacancy__body">
		<div class="container">
			<div class="row">
				<div class="col-lg-8">
					<?php
					foreach ( $sections as $title => $body ) {
						if ( ! $body ) {
							continue;
						}
						?>
					<h2><?= esc_html( $title ); ?></h2>
					<?= wp_kses_post( wpautop( $body ) ); ?>
						<?php
					}
					if ( $equality_inclusion ) {
						?>
					<div class="cb-vacancy__equality"><?= wp_kses_post( wpautop( $equality_inclusion ) ); ?></div>
						<?php
					}
					?>
				</div>
				<aside class="col-lg-4">
					<div class="cb-vacancy__aside">
						<h2>Apply for this role</h2>
						<?php
						if ( function_exists( 'gravity_form' ) ) {
							gravity_form(
								$form_id,
								false,
								false,
								false,
								array( 'role' => get_the_title() ),
								true
							);
						}
						?>
					</div>
				</aside>
			</div>
		</div>
	</section>
</main>
<?php
// Google JobPosting JSON-LD. Ported from cb-turnpower2025.
$schema = array(
	'@context'           => 'https://schema.org/',
	'@type'              => 'JobPosting',
	'title'              => get_the_title(),
	'description'        => wp_strip_all_tags( (string) $role_purpose ),
	'datePosted'         => $date_posted ? $date_posted : get_the_date( 'Y-m-d' ),
	'employmentType'     => $employment_type,
	'hiringOrganization' => array(
		'@type'  => 'Organization',
		'name'   => get_bloginfo( 'name' ),
		'sameAs' => home_url(),
	),
);

if ( $valid_through ) {
	$schema['validThrough'] = $valid_through . 'T00:00:00Z';
}

if ( 'remote' === $location_type ) {
	$schema['jobLocationType']               = 'TELECOMMUTE';
	$schema['applicantLocationRequirements'] = array(
		'@type' => 'Country',
		'name'  => 'United Kingdom',
	);
} else {
	// Drop empty parts: Google flags null values in PostalAddress.
	$address = array_filter(
		array(
			'streetAddress'   => $street_address,
			'addressLocality' => $address_locality,
			'addressRegion'   => $address_region,
			'postalCode'      => $postal_code,
			'addressCountry'  => $address_country ? $address_country : 'GB',
		),
		static function ( $value ) {
			return '' !== $value && null !== $value;
		}
	);

	$schema['jobLocation'] = array(
		'@type'   => 'Place',
		'address' => array_merge( array( '@type' => 'PostalAddress' ), $address ),
	);
}

if ( 'range' === $salary_type && ( $minimum_salary || $maximum_salary ) ) {
	$schema['baseSalary'] = array(
		'@type'    => 'MonetaryAmount',
		'currency' => $salary_currency,
		'value'    => array(
			'@type'    => 'QuantitativeValue',
			'minValue' => (float) $minimum_salary,
			'maxValue' => (float) $maximum_salary,
			'unitText' => $salary_unit,
		),
	);
}

echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
