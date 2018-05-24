<?php
    global $wp;
    $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
    $post_id = url_to_postid( $current_url );
    $enable_readonline = get_post_meta( $post_id, 'enable_readonline', true );
    $epub_file_attachment = get_post_meta( $post_id, 'epub_file_attachment', true );
    if ($epub_file_attachment != "")// and $enable_readonline == TRUE)
    {
        $epub_file_url = $epub_file_attachment['url'];
    }
    $baseurl = esc_url( plugins_url( '/', __FILE__ ) );
?>
<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo get_the_title( $post_id ); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <meta name="apple-mobile-web-app-capable" content="yes">

        <link rel="stylesheet" href="<?php echo $baseurl; ?>css/normalize.css">
        <link rel="stylesheet" href="<?php echo $baseurl; ?>css/main.css">
        <link rel="stylesheet" href="<?php echo $baseurl; ?>css/popup.css">

        <script src="<?php echo $baseurl; ?>js/libs/jquery.min.js"></script>

        <script src="<?php echo $baseurl; ?>js/libs/zip.min.js"></script>

        <script>
            "use strict";

            document.onreadystatechange = function () {
              if (document.readyState == "complete") {
                EPUBJS.filePath = "<?php echo $baseurl; ?>js/libs/";
                EPUBJS.cssPath = "<?php echo $baseurl; ?>css/";
                // fileStorage.filePath = EPUBJS.filePath;

                window.reader = ePubReader("<?php echo $epub_file_url; ?>");
              }
            };

        </script>

        <!-- File Storage -->
        <!-- <script src="<?php echo $baseurl; ?>/js/libs/localforage.min.js"></script> -->

        <!-- Full Screen -->
        <script src="<?php echo $baseurl; ?>js/libs/screenfull.min.js"></script>

        <!-- Render -->
        <script src="<?php echo $baseurl; ?>js/epub.min.js"></script>

        <!-- Hooks -->
        <script src="<?php echo $baseurl; ?>js/hooks.min.js"></script>

        <!-- Reader -->
        <script src="<?php echo $baseurl; ?>js/reader.min.js"></script>

        <!-- Plugins -->
        <!--<script src="<?php echo $baseurl; ?>js/plugins/search.js"></script> -->

        <!-- Highlights -->
        <script src="<?php echo $baseurl; ?>js/libs/jquery.highlight.js"></script>
        <script src="<?php echo $baseurl; ?>js/hooks/extensions/highlight.js"></script>

    </head>
    <body>
      <div id="sidebar">
        <div id="panels">
          <!--<input id="searchBox" placeholder="search" type="search">

          <a id="show-Search" class="show_view icon-search" data-view="Search">Search</a>-->
          <a id="show-Toc" class="show_view icon-list-1 active" data-view="Toc">TOC</a>
          <a id="show-Bookmarks" class="show_view icon-bookmark" data-view="Bookmarks">Bookmarks</a>
          <a id="show-Notes" class="show_view icon-edit" data-view="Notes">Notes</a>

        </div>
        <div id="tocView" class="view">
        </div>
        <!--<div id="searchView" class="view">
          <ul id="searchResults"></ul>
        </div>-->
        <div id="bookmarksView" class="view">
          <ul id="bookmarks"></ul>
        </div>
        <div id="notesView" class="view">
          <div id="new-note">
            <textarea id="note-text"></textarea>
            <button id="note-anchor">Anchor</button>
          </div>
          <ol id="notes"></ol>
        </div>
      </div>
      <div id="main">

        <div id="titlebar">
          <div id="opener">
            <a id="slider" class="icon-menu">Menu</a>
          </div>
          <div id="metainfo">
            <span id="book-title"></span>
            <span id="title-seperator">&nbsp;&nbsp;–&nbsp;&nbsp;</span>
            <span id="chapter-title"></span>
          </div>
          <div id="title-controls">
            <a id="bookmark" class="icon-bookmark-empty">Bookmark</a>
            <a id="setting" class="icon-cog">Settings</a>
            <a id="fullscreen" class="icon-resize-full">Fullscreen</a>
            <a href="<?php echo get_permalink($post_id); ?>" id="returntobook" class="icon-cancel-circled">Return to book</a>
          </div>
        </div>

        <div id="divider"></div>
        <div id="prev" class="arrow">‹</div>
        <div id="viewer"></div>
        <div id="next" class="arrow">›</div>

        <div id="loader"><img src="<?php echo $baseurl; ?>img/loader.gif"></div>
      </div>
      <div class="modal md-effect-1" id="settings-modal">
          <div class="md-content">
              <h3>Settings</h3>
              <div>
                  <p>
                    <input type="checkbox" id="sidebarReflow" name="sidebarReflow">Reflow text when sidebars are open.
                  </p>
              </div>
              <div class="closer icon-cancel-circled"></div>
          </div>
      </div>
      <div class="overlay"></div>
    </body>
</html>