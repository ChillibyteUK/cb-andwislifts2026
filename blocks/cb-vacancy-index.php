<?php
/**
 * Block template for CB Vacancy Index.
 *
 * Ported from cb-turnpower2025's careers index. Queries published vacancies
 * and renders a card listing, hiding roles whose closing date has passed.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-vacancy-index-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$empty_msg  = get_field( 'no_vacancies_message' );

if ( ! $empty_msg ) {
	$empty_msg = 'We do not have any vacancies open right now. Send us your CV and we will keep you in mind.';
}

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

// Hide roles whose closing date has passed. Vacancies with no closing date
// set stay listed until they are unpublished.
$jobs = new WP_Query(
	array(
		'post_type'      => 'vacancy',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => 'valid_through',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'valid_through',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => 'valid_through',
				'value'   => gmdate( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	)
);
?>
<section class="cb-vacancy-index <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			?>
		</div>
		<?php
		if ( $jobs->have_posts() ) {
			?>
		<div class="row g-3">
			<?php
			while ( $jobs->have_posts() ) {
				$jobs->the_post();
				$job_id     = get_the_ID();
				$emp_type   = get_field( 'employment_type', $job_id );
				$tenure_val = get_field( 'tenure', $job_id );
				$loc_type   = get_field( 'location_type', $job_id );
				$locality   = get_field( 'address_locality', $job_id );
				$sal_type   = get_field( 'salary_type', $job_id );
				$summary    = trim( (string) get_field( 'role_purpose', $job_id ) );
				$excerpt    = mb_strimwidth( wp_strip_all_tags( $summary ), 0, 180, '...' );

				if ( 'remote' === $loc_type ) {
					$location_label = 'Remote';
				} elseif ( 'hybrid' === $loc_type ) {
					$location_label = 'Hybrid' . ( $locality ? ' / ' . $locality : '' );
				} else {
					$location_label = $locality ? $locality : 'On site';
				}

				if ( 'range' === $sal_type ) {
					$min      = (int) get_field( 'minimum_salary', $job_id );
					$max      = (int) get_field( 'maximum_salary', $job_id );
					$unit     = get_field( 'salary_unit', $job_id );
					$unit_str = $unit_map[ $unit ] ?? strtolower( (string) $unit );
					if ( $min && $max ) {
						$salary_label = '£' . number_format( $min ) . ' - £' . number_format( $max ) . ' ' . $unit_str;
					} elseif ( $min || $max ) {
						$salary_label = '£' . number_format( $min ? $min : $max ) . '+ ' . $unit_str;
					} else {
						$salary_label = 'On application';
					}
				} else {
					$salary_label = 'On application';
				}
				?>
			<div class="col-lg-4 col-md-6">
				<article class="cb-vacancy-card h-100">
					<div class="cb-vacancy-card__meta">
						<?php
						if ( ! empty( $emp_labels[ $emp_type ] ) ) {
							?>
						<span class="cb-vacancy-card__badge"><?= esc_html( $emp_labels[ $emp_type ] ); ?></span>
							<?php
						}
						if ( ! empty( $tenure_labels[ $tenure_val ] ) ) {
							?>
						<span class="cb-vacancy-card__badge"><?= esc_html( $tenure_labels[ $tenure_val ] ); ?></span>
							<?php
						}
						?>
						<span class="cb-vacancy-card__badge cb-vacancy-card__badge--location"><?= esc_html( $location_label ); ?></span>
					</div>
					<h3><?= esc_html( get_the_title() ); ?></h3>
					<p class="cb-vacancy-card__salary"><?= esc_html( $salary_label ); ?></p>
					<?php
					if ( $excerpt ) {
						?>
					<p class="cb-vacancy-card__excerpt"><?= esc_html( $excerpt ); ?></p>
						<?php
					}
					?>
					<a href="<?= esc_url( get_permalink() ); ?>" class="cb-vacancy-card__link stretched-link">View role</a>
				</article>
			</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
			<?php
		} else {
			?>
		<p class="cb-vacancy-index__empty"><?= esc_html( $empty_msg ); ?></p>
			<?php
		}
		?>
	</div>
</section>
