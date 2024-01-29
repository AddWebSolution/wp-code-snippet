<?php 

/*
Template Name: Resource Center
*/

get_header();

// Determine the current language based on the 'HTTP_X_GT_LANG' header or default to 'EN'
$current_language = isset($_SERVER['HTTP_X_GT_LANG']) ? strtoupper($_SERVER['HTTP_X_GT_LANG']) : 'EN';
?>

<!-- Hero -->
<section id="hero" class="dark resource-center">
    <div class="grid">
		<div class="title-container full padding-100">
			<h1 class="large">Resources</h1>
		</div>
		<!-- Featured Post -->
		<div class="content-container half">
			<?php
				$featured_resource = get_field('featured_resource');
				if($featured_resource):
				// setup post data
				setup_postdata($featured_resource);
				$fr_thumbnail = get_the_post_thumbnail($featured_resource->ID, 'full');
				$fr_title = get_the_title($featured_resource->ID);
				$fr_permalink = get_permalink($featured_resource->ID);
				$fr_excerpt = get_the_excerpt($featured_resource->ID);
				$card_type = get_field('card_type', $featured_resource->ID );
				$resource_link = get_field('resource_link', $featured_resource->ID );
				$resource_types = get_the_terms($featured_resource->ID, 'resource_content_types');
			?>
			<div class="thumbnail-container">
				<!-- THUMB LINK -->
				<?php if($card_type == 'link') : ?>
					<a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fr_thumbnail; ?></a>
				<?php elseif($card_type === 'video') : ?>
					<!-- Video Player -->
					<a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>" target="_blank"><?php echo $fr_thumbnail; ?></a>
				<?php else : ?>
					<a href="<?php echo the_permalink(); ?>"><?php echo $fr_thumbnail; ?></a>
				<?php endif; ?>
			</div>
			<div class="category-container dark">
				<?php
					// Check if the selected post has resource types
					if ($resource_types && !is_wp_error($resource_types)) {
						foreach ($resource_types as $resource_type) {
							echo '<span class="category">' . esc_html($resource_type->name) . '</span>';
						}
					} else {
						echo '<p>No resource types found for the featured resource.</p>';
					}
				?>
			</div>
			<!-- HEADING LINK -->
            <?php if($card_type === 'link') : ?>
				<h2><a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fr_title; ?></a></h2>
			<?php elseif($card_type === 'video') : ?>
				<!-- Video Player -->
				<h2><a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>"><?php echo $fr_title; ?></a></h2>
			<?php else : ?>
				<h2><a href="<?php echo the_permalink(); ?>"><?php echo $fr_title; ?></a></h2>
			<?php endif; ?>
			<p><?php echo $fr_excerpt; ?></p>
			<!-- TEXT LINK -->
            <?php if($card_type === 'link') : ?>
				<a href="<?php echo $resource_link; ?>" class="text-link" target="_blank">Download</a>
			<?php elseif($card_type === 'video') : ?>
				<!-- Video Player -->
				<a href="<?php echo $resource_link; ?>" class="text-link play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>" target="_blank">Watch</a>
			<?php else : ?>
				<a href="<?php echo the_permalink(); ?>" class="text-link">Read More</a>
			<?php endif; ?>
			<?php wp_reset_postdata(); endif; ?>
		</div>

		<!-- Featured Container Section -->
		<div class="featured-container half">
			<img src="/wp-content/themes/Digibee/assets/images/featured-picks.svg" alt="">
			<h3>Featured Picks</h3>
			<div class="featured-picks">
				<!-- Pick 1 -->
				<?php
					$fpick_1 = get_field('featured_pick_1');
					if($fpick_1):
					// setup post data
					setup_postdata($fpick_1);
					$fpick_1_thumbnail = get_the_post_thumbnail($fpick_1->ID, 'full');
					$fpick_1_title = get_the_title($fpick_1->ID);
					$fpick_1_permalink = get_permalink($fpick_1->ID);
					$fpick_1_excerpt = get_the_excerpt($fpick_1->ID);
					$card_type = get_field('card_type', $fpick_1->ID );
					$resource_link = get_field('resource_link', $fpick_1->ID );
					$resource_types = get_the_terms($fpick_1->ID, 'resource_content_types');
				?>
				<div class="pick-container">
					<div class="image-container">
						<!-- THUMB LINK -->
						<?php if($card_type == 'link') : ?>
							<a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_1_thumbnail; ?></a>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>" target="_blank"><?php echo $fpick_1_thumbnail; ?></a>
						<?php else : ?>
							<a href="<?php echo $fpick_1_permalink; ?>"><?php echo $fpick_1_thumbnail; ?></a>
						<?php endif; ?>
					</div>
					<div class="content-container">
						<div class="category-container">
							<?php
								// Check if the selected post has resource types
								if ($resource_types && !is_wp_error($resource_types)) {
									foreach ($resource_types as $resource_type) {
										echo '<span class="category">' . esc_html($resource_type->name) . '</span>';
									}
								} else {
									echo '<p>No resource types found for the featured resource.</p>';
								}
							?>
						</div>
						<!-- HEADING LINK -->
			            <?php if($card_type === 'link') : ?>
							<p class="lead"><a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_1_title; ?></a></p>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<p class="lead"><a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>"><?php echo $fpick_1_title; ?></a></p>
						<?php else : ?>
							<p class="lead"><a href="<?php echo $fpick_1_permalink; ?>"><?php echo $fpick_1_title; ?></a></p>
						<?php endif; ?>
					</div>
				</div>
				<?php wp_reset_postdata(); endif; ?>
				<!-- Pick 2 -->
				<?php
					$fpick_2 = get_field('featured_pick_2');
					if($fpick_2):
					// setup post data
					setup_postdata($fpick_2);
					$fpick_2_thumbnail = get_the_post_thumbnail($fpick_2->ID, 'full');
					$fpick_2_title = get_the_title($fpick_2->ID);
					$fpick_2_permalink = get_permalink($fpick_2->ID);
					$fpick_2_excerpt = get_the_excerpt($fpick_2->ID);
					$card_type = get_field('card_type', $fpick_2->ID );
					$resource_link = get_field('resource_link', $fpick_2->ID );
					$resource_types = get_the_terms($fpick_2->ID, 'resource_content_types');
				?>
				<div class="pick-container">
					<div class="image-container">
						<!-- THUMB LINK -->
						<?php if($card_type == 'link') : ?>
							<a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_2_thumbnail; ?></a>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>" target="_blank"><?php echo $fpick_2_thumbnail; ?></a>
						<?php else : ?>
							<a href="<?php echo $fpick_1_permalink; ?>"><?php echo $fpick_2_thumbnail; ?></a>
						<?php endif; ?>
					</div>
					<div class="content-container">
						<div class="category-container">
							<?php
								// Check if the selected post has resource types
								if ($resource_types && !is_wp_error($resource_types)) {
									foreach ($resource_types as $resource_type) {
										echo '<span class="category">' . esc_html($resource_type->name) . '</span>';
									}
								} else {
									echo '<p>No resource types found for the featured resource.</p>';
								}
							?>
						</div>
						<!-- HEADING LINK -->
			            <?php if($card_type === 'link') : ?>
							<p class="lead"><a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_2_title; ?></a></p>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<p class="lead"><a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>"><?php echo $fpick_2_title; ?></a></p>
						<?php else : ?>
							<p class="lead"><a href="<?php echo $fpick_2_permalink; ?>"><?php echo $fpick_2_title; ?></a></p>
						<?php endif; ?>
					</div>
				</div>
				<?php wp_reset_postdata(); endif; ?>
				<!-- Pick 3 -->
				<?php
					$fpick_3 = get_field('featured_pick_3');
					if($fpick_3):
					// setup post data
					setup_postdata($fpick_3);
					$fpick_3_thumbnail = get_the_post_thumbnail($fpick_3->ID, 'full');
					$fpick_3_title = get_the_title($fpick_3->ID);
					$fpick_3_permalink = get_permalink($fpick_3->ID);
					$fpick_3_excerpt = get_the_excerpt($fpick_3->ID);
					$card_type = get_field('card_type', $fpick_3->ID );
					$resource_link = get_field('resource_link', $fpick_3->ID );
					$resource_types = get_the_terms($fpick_3->ID, 'resource_content_types');
				?>
				<div class="pick-container">
					<div class="image-container">
						<!-- THUMB LINK -->
						<?php if($card_type == 'link') : ?>
							<a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_3_thumbnail; ?></a>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>" target="_blank"><?php echo $fpick_3_thumbnail; ?></a>
						<?php else : ?>
							<a href="<?php echo $fpick_1_permalink; ?>"><?php echo $fpick_3_thumbnail; ?></a>
						<?php endif; ?>
					</div>
					<div class="content-container">
						<div class="category-container">
							<?php
								// Check if the selected post has resource types
								if ($resource_types && !is_wp_error($resource_types)) {
									foreach ($resource_types as $resource_type) {
										echo '<span class="category">' . esc_html($resource_type->name) . '</span>';
									}
								} else {
									echo '<p>No resource types found for the featured resource.</p>';
								}
							?>
						</div>
						<!-- HEADING LINK -->
			            <?php if($card_type === 'link') : ?>
							<p class="lead"><a href="<?php echo $resource_link; ?>" target="_blank"><?php echo $fpick_3_title; ?></a></p>
						<?php elseif($card_type === 'video') : ?>
							<!-- Video Player -->
							<p class="lead"><a href="<?php echo $resource_link; ?>" class="play-video" data-youtube-url="<?php echo digiGetYoutubeEmbedUrl($resource_link); ?>"><?php echo $fpick_3_title; ?></a></p>
						<?php else : ?>
							<p class="lead"><a href="<?php echo $fpick_3_permalink; ?>"><?php echo $fpick_3_title; ?></a></p>
						<?php endif; ?>
					</div>
				</div>
				<?php wp_reset_postdata(); endif; ?>
			</div>
		</div>
    </div>
</section>
<!-- Recent Blogs -->
<section id="recent-blogs" class="dark padding-top-0">
	<div class="grid">
		<div class="title-container full padding-40 with-button">
			<h2>Recent Blogs</h2>
			<a href="/blog/" id="button">View All Blogs</a>
		</div>
		<div class="full">
			<hr class="dashed-line">
		</div>
	</div>
	<div class="grid gap-75 stretch">
		<?php
			// create a new instance of WP_Query to get posts of the same category
			$posts_query = new WP_Query( array(
				'post_type' => 'post',
				'posts_per_page' => 3,
				'orderby' => 'date',
				'order' => 'DESC',
				'tax_query' => array(
					array(
						'taxonomy' => 'resource_language',
						'field'    => 'slug',
						'terms'    => strtolower($current_language),
					),
				),
			) );
			// display the posts
			if ( $posts_query->have_posts() ) { 
				while ( $posts_query->have_posts() ) {
					$posts_query->the_post();
					?>
					<div id="blog-card" class="third small">
						<div class="image-container">
							<a href="<?php echo the_permalink(); ?>"><?php the_post_thumbnail('large'); ?></a>
						</div>
						<div class="content-container">
							<div id="category-container">
								<?php
									$categories = get_the_category();
								
									if ($categories) {
										foreach ($categories as $category) {
											// Exclude 'hide-language' category
											if ($category->slug !== 'hide-language') {
												echo '<span class="category">' . esc_html($category->name) . '</span>';
											}
										}
									}
								?>
							</div>
							<h3><a href="<?php echo the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="body-sm">
								<?php
									if (has_excerpt()) {
										// If the post has a manual excerpt, display it
										the_excerpt();
									} else {
										// If there's no manual excerpt, display the first sentence of the content
										$content = get_the_content();
										$content = strip_shortcodes($content);
										$content = apply_filters('the_content', $content);
										$content = str_replace(']]>', ']]&gt;', $content);

										$excerpt_length = apply_filters('excerpt_length', 20);
										$excerpt_more = apply_filters('excerpt_more', ' ' . '...');
										$content = wp_trim_words($content, $excerpt_length, $excerpt_more);

										echo $content . '...';
									}
								?>
							</p>
							<a href="<?php echo the_permalink(); ?>" class="text-link">Read more</a>
						</div>
					</div>
				<?php } ?>
				<?php
				wp_reset_postdata(); // reset the query
			} else {
				echo '<div class="full">No posts found for this search.</div>';
			}
		?>
	</div>
</section>
<!-- Resource Archive -->
<section id="archive" class="basic">
	<div class="grid filter-bar">
		<div class="title-container full padding-40 with-button">
			<h2>Resources</h2>
			<a href="/resources/" id="button">View All Resources</a>
		</div>
		<!-- Filter Options -->
		<div class="title-container category-container padding-40 three-quarters">

			<!-- Topic -->
			<div id="select-box">
				<span class="text">Topic</span>
				<span class="arrow"><span class="triangle"></span></span>
				<div class="cat-list">
					<?php $categories = get_terms( array(
						'taxonomy' => 'resource_topic',
						'hide_empty' => true,
					)); ?>
					<?php foreach($categories as $category) : ?>
						<a class="cat-list_item active topic" data-topic="<?= $category->slug; ?>"><?= $category->name; ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		
			<!-- Content Type -->
			<div id="select-box">
				<span class="text">Content Type</span>
				<span class="arrow"><span class="triangle"></span></span>
				<div class="cat-list">
					<?php $categories = get_terms( array(
						'taxonomy' => 'resource_content_types',
						'hide_empty' => true,
					)); ?>
					<?php foreach($categories as $category) : ?>
						<a class="cat-list_item active content-type" data-content-type="<?= $category->slug; ?>"><?= $category->name; ?></a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Industry -->
			<div id="select-box">
				<span class="text">Industry</span>
				<span class="arrow"><span class="triangle"></span></span>
				<div class="cat-list">
					<?php $categories = get_terms( array(
						'taxonomy' => 'resource_industry',
						'hide_empty' => true,
					)); ?>
					<?php foreach($categories as $category) : ?>
						<a class="cat-list_item active industry" data-industry="<?= $category->slug; ?>"><?= $category->name; ?></a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Persona -->
			<div id="select-box">
				<span class="text">Persona</span>
				<span class="arrow"><span class="triangle"></span></span>
				<div class="cat-list">
					<?php $categories = get_terms( array(
						'taxonomy' => 'resource_persona',
						'hide_empty' => true,
					)); ?>
					<?php foreach($categories as $category) : ?>
						<a class="cat-list_item active persona" data-persona="<?= $category->slug; ?>"><?= $category->name; ?></a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Filter Button -->
			<a href="" id="button" class="filter-button">Filter</a>

        </div>
        <div class="title-container quarter">
            <?php include(locate_template('resources-searchform.php')); ?>
        </div>
		<div class="full">
			<hr class="dashed-line padding-0">
		</div>
	</div>
	<!-- Resource Display Area -->
	<div class="grid archive gap-75 stretch">
		<?php

			// create a new instance of WP_Query to get posts of the same category
			$posts_query = new WP_Query( array(
				'post_type' => 'resource',
				'posts_per_page' => 6,
				'orderby' => 'date',
				'order' => 'DESC',
				'tax_query' => array(
			        array(
			            'taxonomy' => 'resource_language', // Replace with your actual taxonomy name
			            'field'    => 'slug',
			            'terms'    => strtolower($current_language),
			        ),
			    ),
			) );
			// display the posts
			if ( $posts_query->have_posts() ) { 
				while ( $posts_query->have_posts() ) {
					$posts_query->the_post();
					?>
					
					<?php get_template_part( 'parts/resource-card' ); ?>

				<?php } ?>
				<?php
				wp_reset_postdata(); // reset the query
			} else {
				echo '<div class="full">No posts found for this search.</div>';
			}
		?>
	</div>
</section>
<!-- Blog Signup Section -->
<section id="blog-signup" class="dark">
	<div class="grid">
		<!-- Signup Content Container -->
		<div class="half-center text-center">
			<!-- Signup Title -->
			<h2>Sign up for Digibee updates</h2>

			<!-- Signup Description -->
			<p class="lead">Connect with Digibee and get the latest news and insights.</p>

			<!-- HubSpot Forms Script -->
			<script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/embed/v2.js"></script>

			<!-- HubSpot Forms Initialization Script -->
			<script>
			hbspt.forms.create({
				region: "na1",
				portalId: "20761581",
				formId: "5e8f1718-034b-4061-b622-d9209a5415f1"
			});
			</script>
		</div>
	</div>
</section>

<script>
	(function($) {
		// Initialize variables to store filter parameters
		var topicString = '';
		var topic = '';
		var contenttypeString = '';
		var contenttype = '';
		var industryString = '';
		var industry = '';
		var personaString = '';
		var persona = '';

		// Event handler for clicking/tapping on category items
		$('.cat-list .cat-list_item').on('click tap', function(e) {

			// Check if the clicked item belongs to the 'topic' category
			if ($(e.target).hasClass('topic')) {
				topic = $(this).data('topic');
				topicString = 'resource_topic=' + topic;
			}

			// Check if the clicked item belongs to the 'content-type' category
			if ($(e.target).hasClass('content-type')) {
				contenttype = $(this).data('content-type');
				contenttypeString = '&resource_content_types=' + contenttype;
			}

			// Check if the clicked item belongs to the 'industry' category
			if ($(e.target).hasClass('industry')) {
				industry = $(this).data('industry');
				industryString = '&resource_industry=' + industry;
			}

			// Check if the clicked item belongs to the 'persona' category
			if ($(e.target).hasClass('persona')) {
				persona = $(this).data('persona');
				personaString = '&resource_persona=' + persona;
			}

			// Build the filter URL based on selected parameters and update the button's href attribute
			$('a#button.filter-button').attr('href',
			'/resources/?' + topicString + contenttypeString + industryString + personaString + '#archive');
			
		});
		
	})( jQuery );

</script>

<?php get_footer(); ?>