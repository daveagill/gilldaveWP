<?php

/**
 * Sets up the content width value based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 625;

function gilldave_customize_register( $wp_customize )
{
	$wp_customize->add_setting( 'site-title-slug' , array(
		'default' => get_bloginfo( 'name' ),
	) );
	
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'site-title-slug-control', array(
		'label'        => 'Site Title Slug',
		'section'    => 'title_tagline',
		'settings'   => 'site-title-slug',
	) ) );
}
add_action( 'customize_register', 'gilldave_customize_register' );

function gilldave_setup() {

	// This theme styles the visual editor with editor-style.css to match the theme style
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments
	add_theme_support( 'automatic-feed-links' );

	// Register the main menu
	register_nav_menu( 'primary', 'Primary Menu' );

	// Add support for featured-images
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'gilldave_setup' );


/**
 * Enqueues scripts and styles
 */
function twentytwelve_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );


	/*
	 * Loads our Google font.
	 */
	$protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'Open+Sans:400italic,700italic,400,700',
		'subset' => 'latin,latin-ext',
	);
	wp_enqueue_style( 'twentytwelve-fonts', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
}
add_action( 'wp_enqueue_scripts', 'twentytwelve_scripts_styles' );

/**
 * Customises the value of wp_title
 */
function twentytwelve_wp_title( $title, $sep ) {
	global $paged, $page;

	// don't touch feeds, see: http://core.trac.wordpress.org/ticket/21233#comment:9
	if ( is_feed() )
		return $title;
		
	// add the URL.
	$title .= get_theme_mod( 'site-title-slug' );
		
	// leave it at that for the homepage
	if ( is_front_page() ) {
		return $title;
	}

	// add a page number if necessary
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( 'Page %s', max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );



/**
 * Registers our main widget area and the front page widget areas.
 */
function twentytwelve_widgets_init() {
	register_sidebar( array(
		'name' => 'Blog Sidebar',
		'id' => 'blog-sidebar',
		'description' => 'Appears on posts and default pages',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => 'Homepage Sidebar',
		'id' => 'homepage-sidebar',
		'description' => 'Appears on the homepage',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'twentytwelve_widgets_init' );

/**
 * Displays navigation to next/previous pages when applicable.
 */
function twentytwelve_content_nav( $html_id ) {
	global $wp_query;

	$html_id = esc_attr( $html_id );

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>
			<div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
		</nav><!-- #<?php echo $html_id; ?> .navigation -->
	<?php endif;
}

/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 */
function twentytwelve_entry_meta() {
	// used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( ', ' );

	// used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', ', ' );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}


/**
 * Extends the default WordPress body class
 */
function twentytwelve_body_class( $classes ) {

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
	}

	$classes[] = 'custom-font-enabled';
	$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'twentytwelve_body_class' );


