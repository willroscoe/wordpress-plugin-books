<html>
<head>
<?php
    global $wp;
    $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
    $postid = url_to_postid( $current_url );
    $enable_readonline = get_post_meta( $postid, 'enable_readonline', true );
    $epub_file_attachment = get_post_meta( $postid, 'epub_file_attachment', true );
    if ($epub_file_attachment != "")// and $enable_readonline == TRUE)
    {
        $epub_file_url = $epub_file_attachment['url'];
    }
?>

</head>
<body>
    <h1>Reader</h1>
    <p>Current url: <?php echo $current_url; ?></p>
    <p>post id: <?php echo $postid; ?> </p>
    <p>enable_readonline: <?php echo $enable_readonline; ?> </p>
    <p>book url: <?php echo $epub_file_url; ?> </p>
</body>
</html>