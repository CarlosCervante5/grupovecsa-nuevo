<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package motormania
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( has_post_thumbnail() ): ?>
		<div class="post_thumbnail">
			<?php the_post_thumbnail() ?>
		</div>
	<?php endif ?>
	<div class="entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'motormania' ),
				'after'  => '</div>',
			) );
		?>
	</div>
	
</article><!-- #post-<?php the_ID(); ?> -->
