<?php
/**
 * The template for displaying actual book content from epub book
 */
global $wp;
require_once(dirname(__FILE__) . '/ePubServer.php');

$basepath = "/books/" . get_query_var('wr_book') . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('read'); // get the url part after '/read/'
//$uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
//echo utf8_decode(urldecode($_SERVER['REQUEST_URI']));
//phpinfo();
//die;
$book_full_filesystem_path = get_book_full_filesystem_path();

$epub = new ePubServer($book_full_filesystem_path, $basepath, $asset_to_process);
$epub->processRequest();

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
		<ul class='link-block'>
			<?php if ($downloadlinks != "") : ?>
				<li><span class='label'>Download</span> <span class='links'><?php echo $downloadlinks ?></span></li>
			<?php endif; ?>

			<?php if ($buy_book_link != "") : ?>
				<li><span class='label'>Buy</span> <span class='links'><a href='<?php echo $buy_book_link ?>'>Paperback</a></a></li>
			<?php endif; ?>
		</ul>
	</section>
    <section class="book-toc">
    <?php 
        $epub->displayTableOfContents();
    ?>
    </section>
</aside><!-- #book-sidebar -->

<?php get_footer(); ?>
