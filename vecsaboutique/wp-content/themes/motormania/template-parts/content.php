<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package motormania
 */

global $motormania_opt;
 
$social_share = !empty( $motormania_opt['social_share'] ) ? $motormania_opt['social_share'] : '';

?>
<?php if ( is_single() ): ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ): ?>
	<div class="post_thumbnail">
		<?php the_post_thumbnail( 'motormania-1280x720' ) ?>
	</div>
	<?php endif ?>

	<div class="entry-content">
		<div class="entry-meta">
			<ul class="list-inline">
				<li class="list-inline-item">
					<i class="fa fa-user"></i> <?php echo esc_html__( 'by', 'motormania' ) ?> <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>"><?php the_author(); ?></a>
				</li>
				<li class="list-inline-item">
					<i class="fa fa-comment"></i> <a href="<?php comments_link(); ?>"><?php printf( _nx( '1 Comment', '%1$s Comments', get_comments_number(), 'comments title', 'motormania' ), number_format_i18n( get_comments_number() ) );?></a>
				</li>
				<li class="list-inline-item">
					<i class="fa fa-tags"></i> <?php
					$categories = get_the_category();
					if ( ! empty( $categories ) ) {
					    echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
					}?>
				</li>
				<?php if (function_exists('getPostViews')){ ?>
				<li class="list-inline-item">
					<i class="fa fa-eye"></i> <?php echo esc_html__( 'Views', 'motormania' ) ?> <?php echo getPostViews(get_the_id()); ?>
				</li>
				<?php } ?>
			</ul>			
		</div><!-- .entry-meta -->
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'motormania' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'motormania' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->

	<?php if (get_the_tags()): ?>
		<div class="tags">
			<?php the_tags( $before = '',' ','')	 ?>
			<?php if ( $social_share == true ){ 
				if (function_exists('motormania_social_share')) {?>
					<div class="post-share">
				        <?php motormania_social_share(); ?>		        
				    </div><!-- .post-share -->
				<?php }
			} ?>
		</div>
	<?php endif ?>
	

</article>

<?php else: ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="the_excerpt">
		<?php if ( has_post_thumbnail() ): ?>
			<div class="post_thumbnail">
				<a href="<?php the_permalink() ?>">
					<?php the_post_thumbnail( 'motormania-1280x650' ) ?>
				</a>
				<span class="excerpt-date"><?php echo get_the_date() ?></span>
			</div>
		<?php endif ?>
		<div class="the_excerpt_content">
			<div class="entry-meta">
				<ul class="list-inline">
					<li class="list-inline-item">
						<i class="fa fa-user"></i> <?php echo esc_html__( 'by', 'motormania' ) ?> <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>"><?php the_author(); ?></a>
					</li>
					<li class="list-inline-item">
						<i class="fa fa-comment"></i> <a href="<?php comments_link(); ?>"><?php printf( _nx( '1 Comment', '%1$s Comments', get_comments_number(), 'comments title', 'motormania' ), number_format_i18n( get_comments_number() ) );?></a>
					</li>
					<li class="list-inline-item">
						<i class="fa fa-tags"></i> <?php
						$categories = get_the_category();
						if ( ! empty( $categories ) ) {
						    echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
						}?>
					</li>
					<?php if (function_exists('getPostViews')){ ?>
					<li class="list-inline-item">
						<i class="fa fa-eye"></i> <?php echo esc_html__( 'Views', 'motormania' ) ?> <?php echo getPostViews(get_the_id()); ?>
					</li>
					<?php } ?>
				</ul>
			</div><!-- .entry-meta -->
			<header class="entry-header">
				<?php the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
			</header><!-- .entry-header -->

			<p><?php echo wp_trim_words( get_the_content(), 25, '...' ); ?></p>

			<div class="excerpt-readmore">
				<a class="btn" href="<?php the_permalink() ?>"><?php echo esc_html__( 'Read More','motormania' ); ?></a>
			</div>
		</div>
	</div>
</article>
	
<?php endif ?>
