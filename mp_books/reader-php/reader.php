<?php
/**
 * The template for displaying actual book content from epub book
 */
global $wp;
require_once(dirname(__FILE__) . '/ePubServer.php');

$basepath = "/books/" . get_query_var('mp_book') . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('read'); // get the url part after '/read/'
$book_full_filesystem_path = get_book_full_filesystem_path();

$epub = new ePubServer($book_full_filesystem_path, $basepath, $asset_to_process);
$epub->processRequest();

$book_subtitle = get_book_subtitle();
$book_authors = get_book_authors();

// get the theme header
get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		    $epub->displayChapter();
		?>
	</main><!-- .site-main -->
</div><!-- .content-area -->

<!-- .sidebar .widget-area -->
<aside id="book-sidebar" class="sidebar widget-area book-sidebar" role="complementary">
	<section id="subpages-widget-2" class="widget widget_subpages">
    <h2 class="widget-title">Read online</h2>
		<h2><?php the_title(); ?></h2>
		<h3><?php echo $book_subtitle; ?></h3>
		<h4><?php echo $book_authors; ?></h4>
	
		<?php matteringpress_post_thumbnail(); ?>

		<?php get_book_links_block(); ?>

	</section>
    <section class="book-toc">
    <?php 
        $epub->displayTableOfContents();
    ?>
    </section>
</aside><!-- #book-sidebar -->

<?php get_footer(); ?>
