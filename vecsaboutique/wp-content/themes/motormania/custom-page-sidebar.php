<?php 
/**
 * Template Name: Custom Page With Sidebar
 */

get_header(); ?>

<section class="section-padding">
	<div class="container">
		<div class="row justify-content-center">
			<div class="<?php if ( is_active_sidebar('sidebar') ){ echo'col-xl-9 col-md-7'; } else { echo'col-lg-12'; } ?>">
				<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content', 'page' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>
			</div>

			<?php if ( is_active_sidebar('sidebar') ){ ?>
    		<div class="col-xl-3 col-md-5">
    			<?php get_sidebar(); ?>
    		</div>
            <?php } ?>
		</div>
	</div>
</section>

<?php get_footer();