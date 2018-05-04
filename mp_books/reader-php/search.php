<?php
/**
 * The template for displaying search results from epub book
 */
global $wp;
require_once(dirname(__FILE__) . '/ePubServer.php');
$basepath = "/books/" . get_query_var('mp_book');
$basepath_read = $basepath . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('search'); // get the url part after '/search/'
$book_full_filesystem_path = get_book_full_filesystem_path();
$searchterm = $_POST["searchterm"];
// cleanse searchterm

function cleanse_input($data) {
    // TODO delete if less than 3 chars long
    
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = trim($data);
    $blacklist = array('a', 'and', 'at', 'be', 'but', 'by', 'of', 'i', 'in', 'me', 'my', 'no', 'not', 'on', 'the', 'to', 'too'); // get rid of time wasters
    foreach ($blacklist as $blackitem) {
        if ($data == $blackitem) {
            $data = "";
            break;
        }
    }
    if (strlen($data) < 3) {
        $data = "";
    }

    return $data;
  }

$epub = new ePubServer($book_full_filesystem_path, $basepath_read, 'search', $searchterm);

$book_subtitle = get_book_subtitle();
$book_authors = get_book_authors();

// get the theme header
get_header(); 
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		    $epub->displaySearchResults();
		?>
	</main><!-- .site-main -->
</div><!-- .content-area -->

<!-- .sidebar .widget-area -->
<aside id="book-sidebar" class="sidebar widget-area book-sidebar" role="complementary">
	<section id="subpages-widget-2" class="widget widget_subpages">
    <h2 class="widget-title">Read online</h2>
		<h2><?php the_title(); ?></h2>
		<h3 class="book-subtitle"><?php echo $book_subtitle; ?></h3>
		<h4 class="book-authors"><?php echo $book_authors; ?></h4>
	
		<?php matteringpress_post_thumbnail(); ?>

		<?php get_book_links_block(); ?>

	</section>
    <section id="search-book-form" class="widget widget_search-book">
        <h2 class="widget-title">Search in book</h2>
        <form action="<?php echo $basepath . '/search' ?>" method="post"><input type="text" name="searchterm" /> <input type="submit" name="submit" value="submit" /></form>
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
