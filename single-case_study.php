<?php
/**
 * Single Case Study template.
 *
 * Renders the brief's fixed case study shape - challenge, what we did, result -
 * from ACF fields, falling back to the post body for studies imported as prose
 * that have not yet been split into those sections.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$case_id = get_the_ID();

$client    = get_field( 'client_name', $case_id );
$challenge = get_field( 'challenge', $case_id );
$did       = get_field( 'what_we_did', $case_id );
$result    = get_field( 'result', $case_id );
$services  = (array) get_field( 'related_services', $case_id );
$sectors   = (array) get_field( 'related_sectors', $case_id );

$hero_id  = get_post_thumbnail_id( $case_id ) ? get_post_thumbnail_id( $case_id ) : 22;
$hero_url = wp_get_attachment_image_url( $hero_id, 'full' );

$meta = array();

foreach ( $sectors as $sector_id ) {
	$meta[] = get_the_title( $sector_id );
}
foreach ( $services as $service_id ) {
	$meta[] = get_the_title( $service_id );
}

$sections = array(
	'The challenge' => $challenge,
	'What we did'   => $did,
	'The result'    => $result,
);

$has_sections = $challenge || $did || $result;
?>
<main id="main" class="cb-case-study">
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
			<?php
			if ( $client ) {
				?>
			<p class="cb-hero__subline"><?= esc_html( $client ); ?></p>
				<?php
			}
			if ( $meta ) {
				?>
			<p class="cb-hero__intro"><?= esc_html( implode( ' · ', $meta ) ); ?></p>
				<?php
			}
			?>
		</div>
	</section>

	<?php
	// Structured sections are plain prose, so they get a reading measure inside a
	// col-lg-8. The narrative is rendered ACF blocks - each is a full-width section
	// with its own .container and column layout, so it must NOT be nested in a
	// column or it gets squeezed to 66% and split again inside that.
	if ( $has_sections ) {
		?>
	<section class="cb-case-study__body">
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
				<?= wp_kses_post( $body ); ?>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</section>
		<?php
	}

	$content = get_the_content();

	if ( $content ) {
		?>
	<div class="cb-case-study__narrative">
		<?php
		// Rendered block output, already filtered by WordPress. Do NOT pass this
		// through wp_kses_post(): it strips <script> tags but keeps the script body,
		// dumping block JS onto the page as visible text.
		echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
		<?php
	}
	?>

	<?php
	// "More case studies" nav, matching the casenav on Turnpower's project pages.
	$more = new WP_Query(
		array(
			'post_type'      => 'case_study',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'post__not_in'   => array( $case_id ),
			'orderby'        => 'rand',
			'no_found_rows'  => true,
		)
	);

	if ( $more->have_posts() ) {
		?>
	<section class="cb-case-study__more">
		<div class="container">
			<div class="cb-section-head pb-5">
				<h2>More case studies</h2>
			</div>
			<div class="row g-3">
				<?php
				foreach ( $more->posts as $item ) {
					?>
				<div class="col-lg-4 col-md-6">
					<?php cb_case_study_card( $item, 20 ); ?>
				</div>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
		<?php
	}

	// Closing CTA from the brief.
	echo do_blocks(
		'<!-- wp:acf/cb-form ' . wp_json_encode(
			array(
				'name' => 'acf/cb-form',
				'data' => array(
					'heading'  => 'Talk to us about a similar project',
					'_heading' => 'field_cb_form_heading',
					'intro'    => 'Tell us about your building and we will come back to you.',
					'_intro'   => 'field_cb_form_intro',
					'form_id'  => '2',
					'_form_id' => 'field_cb_form_form_id',
				),
				'mode' => 'edit',
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		) . ' /-->'
	);
	?>
</main>
<?php
get_footer();
