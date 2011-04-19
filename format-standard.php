		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header>
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'initializr' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			</header>
	<?php initializr_post_date(); ?>
	<?php if ( ! is_singular() && has_excerpt() ) : /* Only display excerpts for archives and search. */ ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
	<?php else : ?>
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'initializr' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'initializr' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
	<?php endif; ?>

			<footer>
			<?php echo timeAgo(); ?> <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'initializr' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">#</a>
			<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'initializr' ), __( '1 Comment', 'initializr' ), __( '% Comments', 'initializr' ) ); ?></span>
			<?php edit_post_link( __( 'Edit', 'initializr' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
			</footer>	
		</article>