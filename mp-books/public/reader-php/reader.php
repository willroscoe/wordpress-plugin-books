<?php
/**
 * The template for displaying actual book content from epub book
 */
global $wp;
require_once(dirname(__FILE__) . '/ePubServer.php');

$basepath = "/books/" . get_query_var('mp_book');
$basepath_read = $basepath . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('read'); // get the url part after '/read/'
$book_full_filesystem_path = apply_filters( 'mp_book_get_book_full_filesystem_path', FALSE );

if ($book_full_filesystem_path == "") {
    echo "Sorry. I can't find that book.";
}

$epub = new ePubServer($book_full_filesystem_path, $basepath_read, $asset_to_process);
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
        
		<?php do_action( 'mp_books_get_book_meta_info' ); ?>
	
		<?php matteringpress_post_thumbnail(); ?>

		<?php do_action('mp_books_get_book_links'); ?>

	</section>
    <section id="search-book-form" class="widget widget_search-book">
        <h2 class="widget-title">Search in book</h2>
        <form action="<?php echo $basepath . '/search' ?>" method="post"><input class="search-book-input" type="text" name="searchterm" placeholder="Search book" /> <input type="submit" name="submit" value="submit" class="search-book-submit" /></form>
    </section>
    <section class="book-toc">
    <?php 
        $epub->displayTableOfContents();
    ?>
    </section>
</aside><!-- #book-sidebar -->

<script>
    jQuery(function($) {
        $(".epub p").each(function(i,e) {
            $(this).attr('id', 'paragraph-'+(1+i));
        });

        $(window).on('hashchange', function() {
            $(".epub p").removeClass('selected');
            var p = $(".epub p#" + window.location.hash.substr(1));
            if (typeof p !== "undefined") {
                p.addClass('selected');
                $('html, body').animate({
                    scrollTop: p.offset().top
                }, 1000);
            }
        });

        if (window.location.hash) {
            $(window).trigger('hashchange');
        }
        if ($(".epub span.selected")) {
            $('html, body').animate({
                scrollTop: $(".epub span.selected").offset().top
            }, 1000);
        }
    });
</script>
<style>
    .epub .selected { background-color: #fff684; padding: 20px; margin: 10px 0px; }
    .epub span.selected { display: inline; padding:10px 20px; margin:0px; line-height:2; }
</style>

<?php get_footer(); ?>
