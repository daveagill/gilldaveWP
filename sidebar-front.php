<?php
/**
 * The sidebar containing the front page widget areas.
 */
 
if ( is_active_sidebar( 'homepage-sidebar' ) ) : ?>

<div id="secondary" class="widget-area" role="complementary">
	<?php if ( is_active_sidebar( 'homepage-sidebar' ) ) : ?>
	<div class="first front-widgets">
		<?php dynamic_sidebar( 'homepage-sidebar' ); ?>
	</div>
	<?php endif; ?>
</div><!-- #secondary -->

<?php endif; ?>