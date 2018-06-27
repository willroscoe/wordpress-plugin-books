<?php
/**
 * The template for displaying search results from epub book
 */
global $wp;
require_once(dirname(__FILE__) . '/ePubServer.php');
//require_once(dirname(__FILE__) . '/../class-mp-books-public.php');

//$plugin = new MP_Books_Public('mp-books', '1.0.0');

$basepath = "/books/" . get_query_var('mp_book');
$basepath_read = $basepath . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('search'); // get the url part after '/search/'
$book_full_filesystem_path = apply_filters( 'mp_book_get_book_full_filesystem_path', FALSE );

if ($book_full_filesystem_path == "") {
    echo "Sorry. I can't find that book to search in.";
}

//$searchterm = cleanse_input($_POST["searchterm"]); // get and cleanse searchterm

$searchterm = apply_filters( 'mp_book_cleanse_search_terms', $_POST["searchterm"]); // get and cleanse searchterm

$epub = new ePubServer($book_full_filesystem_path, $basepath_read, 'search', $searchterm);

/*function cleanse_input($data) {
    if (strlen($data) < 3)
        return "";
    
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = trim($data);

    if (strlen($data) < 3)
        return "";

    $blacklist = array('the', 'and', 'for', 'you', 'say', 'but', 'his', 'not', 'she', 'can', 'who', 'get', 'her', 'all', 'one', 'out', 'see', 'him', 'now', 'how', 'its', 'our', 'two', 'way', 'new'); // get rid of time wasters
    if (in_array($data, $blacklist))
        return "";  

    return $data;
  }*/

// get the theme header
get_header(); 
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
        <h1>Search Results</h1>
		<?php
            if (strlen($searchterm) > 0) {
                $epub->displaySearchResults();
            } else {
                ?>
                <p>No results.</p>
                <?php
            }  
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
        <form action="<?php echo $basepath . '/search' ?>" method="post"><input class="search-book-input" type="text" name="searchterm" placeholder="Search book" /> <input type="submit" name="submit" value="submit"  class="search-book-submit" /></form>
    </section>
    <section class="book-toc">
    <?php
        $epub->displayTableOfContents();  
    ?>
    </section>
</aside><!-- #book-sidebar -->

<style>
    .selected { background-color: #fff684; padding: 20px; margin: 10px 0px; }
    span.selected { display: inline; padding:10px 20px; margin:0px; line-height:2; }
</style>

<?php get_footer(); ?>
