<?php


    $basepath = "/books/" . get_query_var('wr_book') . "/read"; // path to be added to all the links in the epub book
$asset_to_process = get_query_var('read'); // get the url part after '/read/'
$book_full_filesystem_path = get_book_full_filesystem_path();
echo "basepath: ". $basepath . "\n";
echo "asset_to_process: ". $asset_to_process. "\n";
echo "book_full_filesystem_path: ". $book_full_filesystem_path . "\n";

die;
?>