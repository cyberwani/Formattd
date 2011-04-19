		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'initializr' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'initializr' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
			<footer>
				<?php echo timeAgo(); ?> <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'initializr' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">#</a>
				<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'initializr' ), __( '1 Comment', 'initializr' ), __( '% Comments', 'initializr' ) ); ?></span>
				<?php edit_post_link( __( 'Edit', 'initializr' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
			</footer>

		</article>