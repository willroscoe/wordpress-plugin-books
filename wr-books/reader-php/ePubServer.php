<?php
require_once(dirname(__FILE__) . '/libs/BookGluttonEpub.php');
require_once(dirname(__FILE__) . '/libs/HtmlDomParser.php');
require_once(dirname(__FILE__) . '/libs/lessc.inc.php');

class ePubServer {
	public $lib;
	public $base_link;
	public $asset_to_process;
	public $current_chapterId;
	private $etag;
	private $full_file_path;

	public function __construct($file, $base_link, $asset_to_process) {

		if ((!is_string($file) || (is_string($file) && !file_exists($file)))) {
			throw new \Exception("Cannot open non-existing ePub file " . (is_string($file)?$file:get_class($file)));
		}

		$this->lib = new BookGluttonEpub();
		$this->lib->open($file);

		$this->base_link = $base_link;
		$this->asset_to_process = urldecode($asset_to_process);
		$this->full_file_path = $file;
	}

	// $asset is the url part after /read/
	public function processRequest()
	{
		if ($this->full_file_path && file_exists($this->full_file_path)) {
			$this->etag = md5($this->asset_to_process . "-" . filemtime($this->full_file_path));

			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
				stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == "\"{$this->etag}\"") {
				// Return visit and no modifications, so do not send anything
				header ("HTTP/1.0 304 Not Modified");
				die;
			}
		}

		$filelist = $this->getFilelist();

		// first, we try to find a file matching the exact path
		$match = null;
		foreach ($filelist as $file) {
			// test if the current file ENDS WITH the (full) filename of the asset
			if (strpos(strrev($file['name']), strrev($this->asset_to_process))===0) {
				$match = $file;
			}
		}

		// if that doesn't work, try if we can at least find a file with the same filename
		// (I don't understand some of the epub files out there...)
		if (!$match) {
			$best = array('matching'=>0, 'file'=>null);
			$asset_parts = array_reverse(explode('/', $this->asset_to_process));
			foreach ($filelist as $file) {
				$file_parts = array_reverse(explode('/', $file['name']));
				$m = 0;
				for ($i=0; $i<min(count($file_parts), count($asset_parts)); $i++) {
					if ($file_parts[$i]==$asset_parts[$i]) {
						$m++;
					} else {
						break;
					}
				}
				if ($m>$best['matching']) {
					$best['matching'] = $m;
					$best['file'] = $file;
				}
			}
			if ($best['matching']>0) {
				$match = $best['file'];
			}
		}

		// if we have a match, render the file
		if ($match) {
			$pi = pathinfo($match['name']);
			@$ext = $pi['extension'];

			switch (strtolower($ext)) {
				case "xml":
				case "xhtml":
				case "html":
					// if this is a HTML file of some sorts, then display it as text
					//$this->renderText($match);
					$this->current_chapterId = $this->id($match);
					break;

				case "css":
					// if this is a CSS file, prefix all selectors with our own
					// identifier so that the styling only applies to one part of the page
					$this->renderCSS($match);
					die;
					break;

				default:
					// otherwise, serve it as a file with the appropriate mime-type
					$full_path = $match['name'];
					header("Content-Type: " . $this->getMimeFromExt($full_path));
					header("Etag: \"{$this->etag}\"");
					echo $this->getFile($full_path);
					die;
			}
		}

		// couldn't serve asset - try to see if this is maybe a book chapter we need to display
		$toc = $this->getTableOfContents();
		if ($this->asset_to_process != "")
		{
			$chapter = $this->asset_to_process;
			$chapter_parts = explode('/', $chapter);
			for ($i=0; $i<count($chapter_parts); $i++) {
				foreach ($toc as $toc_entry) {
					// go through each part of the URL and see if it matches the table of contents
					// in the right position
					if (basename($toc_entry['path'],'/')==basename($chapter_parts[$i])) {
						$toc = $toc_entry['children'];

						if ($i==count($chapter_parts)-1) {
							// we've found a chapter to display! Render the appropriate page
							//$this->renderText($this_page, $toc_entry['id']);
							$this->current_chapterId = $this->id($toc_entry['id']);
						}
						break;
					}
				}
			}
		}

		if ($this->current_chapterId == "") // no chapter selected so redirect to first item in the toc
		{
			if (is_array($toc))
			{
				if (count($toc) > 0)
				{
					if (isset($toc[0]['link']))
					{
						header("Location: $link" . $toc[0]['link']);
						die;
					}
				}
			}
		}
	}
	/**
	 * Prefixes all CSS statements with '.epub' so it only applies to a specific part of the page
	 *
	 * @param $this_page
	 * @param $file
	 */
	private function renderCSS($file) {
		$full_path = $file['name'];
		$orig_css = explode("\n", $this->getFile($full_path));

		$imports = $rest = array();
		foreach ($orig_css as $line) {
			if (strpos(strtolower($line), "@import")===0) {
				$imports[] = $line;
			} else {
				$rest[] = $line;
			}
		}
		$imports = implode("\n", $imports);
		$rest = implode("\n", $rest);

		$css = "$imports .epub { $rest }";

		header("Content-Type: text/css");
		header("Etag: \"{$this->etag}\"");

		$less = new lessc;
		echo $less->compile($css);
	}

	private function getMimeFromExt($src) {
		$pi = pathinfo($src);
		@$ext = $pi['extension'];
		switch (strtolower($ext)) {
			case 'svg':
				return 'image/svg+xml';
			case 'png':
				return 'image/png';
			case 'jpg':
				return 'image/jpeg';
			case 'jpeg':
				return 'image/jpeg';
			case 'gif':
				return 'image/gif';
			case 'ttf':
				return 'application/x-font-ttf';
			case 'otf':
				return 'application/x-font-otf';
			case 'xml':
				return 'application/xml';
			case 'html':
				return 'application/xhtml+xml';
			case 'xhtml':
				return 'application/xhtml+xml';
			case 'htm':
				return 'application/xhtml+xml';
			case 'pdf':
				return 'application/pdf';
			case 'css':
				return 'text/css';
			case 'swf':
				return 'application/x-shockwave-flash';
			default:
				return 'application/octet-stream';
		}
	}

	public function getFileList() {
		$path = $this->lib->getPackagePath();

		$dir_iterator = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
// could use CHILD_FIRST if you so wish

		$list = array();
		foreach ($iterator as $file) {
			if (is_file($file)) {
				$list[] = array('name'=>trim(str_replace($path, '', $file), '/'));
			}
		}

		return $list;
	}

	public function getFile($file) {
		$path = $this->lib->getPackagePath();

		return file_get_contents($path . '/' . $file);
	}

	public function getItem($id, $nav=null) {
		if (is_null($nav)) {
			$nav = array_merge($this->getFileList(), $this->lib->getNavPoints());
		}
		foreach ($nav as $item) {
			if ($this->id($item)==$id) {
				return $item;
			}
			if (count($item['navPoints'])>0) {
				$answer = $this->getItem($id, $item['navPoints']);
				if (!is_null($answer)) {
					return $answer;
				}
			}
		}
		return null;
	}

	protected function getNavPoints() {
		$cache_filename = $this->lib->packagepath . '/navpoints.cache';
		if (file_exists($cache_filename)) {
			return unserialize(file_get_contents($cache_filename));
		}

		$nav_points = array();
		$files = $this->getFileList();
		$old_path = null;
		foreach ($this->lib->getNavPoints() as $point) {
			$parts = parse_url($point['src']);
			$partial_path = $parts['path'];
			$partial_fragment = $parts['fragment']; 

			foreach ($files as $f) {
				if (strpos($f['name'], $partial_path) !== false) {
					$full_path = $f['name'];

					if ($old_path!=$full_path) {
						// we found the full path of the file that this nav point is pointing to
						// -> now we figure out *where* exactly in this file (which byte) this nav point starts
						$html = $this->getFile($full_path);

						// we only use the 'body' part of the chapter, and the CSS declarations from the 'head' part
						$parser = new HtmlDomParser();
						/** @var \simple_html_dom $dom */
						$dom = $parser->str_get_html($html);
					}

					if ($dom) {
						$body = $dom->getElementById(ltrim($partial_fragment, '#'));
						$nav_points[] = array('point'=>$point, 'start'=>$body->tag_start);
					}
					$old_path = $full_path;
				}
			}
		}

		file_put_contents($cache_filename, serialize($nav_points));

		return $nav_points;
	}

	public function chapter($id, $__dummy__=false, $search=true, $path=false) {
		$cache_filename =  $this->lib->packagepath . '/chapter-' . md5($id) . '.cache';
		if (file_exists($cache_filename)) {
			$html = file_get_contents($cache_filename);
		} else {
			$nav_points = $this->getNavPoints();

			$full_path = $partial_path = $partial_fragment = false;
			if ($id!==false) {
				$item = $this->getItem($id);
				if (array_key_exists('name', $item)) {
					$full_path = $item['name'];
				}
				if (array_key_exists('src', $item)) {
					$parts = parse_url($item['src']);
					$partial_path = $parts['path'];
					$partial_fragment = $parts['fragment'];
					if ($search) {
						foreach ($this->getFileList() as $file) {
							if (strpos($file['name'], $partial_path)!==false) {
								$full_path = $file['name'];
								break;
							}
						}
					}
				}
			}

			if ($path!==false) {
				$full_path = $path;
			}

			$html = $this->getFile($full_path);

			// adjust relative paths so that they are relative to the current page
			$abs = dirname($full_path);
			$html = preg_replace('/(\.\.\/)+/', $abs . '/$1', $html);

			// we only use the 'body' part of the chapter, and the CSS declarations from the 'head' part
			$parser = new HtmlDomParser();
			/** @var \simple_html_dom $dom */
			$dom = $parser->str_get_html($html);

			if (is_object($dom)) {
				$links = $dom->getElementsByTagName('link');
				if (!$partial_fragment) {
					$body = $dom->getElementByTagName('body');
				} else {
					foreach ($nav_points as $i=>$np) {
						if (strpos($np['point']['src'], $partial_path . "#" . $partial_fragment)!==false) {
							// we found our nav point
							$from = $np['start'];

							// The 'from' position is most certainly SMALLER than the actual tag position.
							// This is a limitation of simple_html_dom, unfortunately. We compensate by
							// using the same simple_html_dom library to find the actual content.
							if ($i < count($nav_points)-1) {
								$to = $nav_points[$i+1]['start'];
								$body = substr($html, $from, $to - $from);
							} else {
								$body = substr($html, $from);
							}

							// now locate the thing we're looking for
							$dom = $parser->str_get_html($body);
							$elem = $dom->getElementById($partial_fragment);
							$body = substr($body, $elem->tag_start);
							break;
						}
					}
				}

				// put the HTML together
				$html = $links;
				if (is_string($body)) {
					$html .= $body;
				} else {
					$html .= $body->innertext();
				}
			}

			file_put_contents($cache_filename, $html);
		}

		// wrap everything in a '.epub' class so we can manipulate the CSS to only apply to this DIV
		//$html = "<div class='epub'>$html</div>";
		$html = '<html><head><base href="' . $this->base_link . '/" target="_self"></head><body><div class="epub">'. $html . '</div></body></html>'; // need to include <base> as wp adds a trailing '/' to all requests which brakes the relative links in the epub html

		// try to force 'correct' HTML, closes dangling tags that might mess up the rest of the page
		$doc = new \DOMDocument('1.0', 'UTF-8');
		libxml_use_internal_errors(true);
		$doc->loadHTML($html); // html content
		libxml_use_internal_errors(false);
		$doc->normalizeDocument();

		// encode all '#' in a href links as wp added a trailing slash before '#'
		/*$xpath = new DOMXpath($doc);
		foreach ($xpath->query('//a[@href]') as $a) {
			$href = $a->getAttribute('href');
			$a->setAttribute('href', urlencode($href));
		}*/

		$html = utf8_decode($doc->saveHTML($doc->documentElement));

		return $html;
	}

	public function originalChapter($id) {
		return $this->chapter($id);
	}

	public function displayChapter($chapter_name_from_url = "")
	{
		$toc = $this->getTableOfContents();

		$chapter = $this->current_chapterId;
		if ($chapter == "" and $chapter_name_from_url != "")
		{
			$chapter = $this->findChapterIdByNameInUrl($chapter_name_from_url);
		}
		$html = $this->originalChapter($chapter);

		// crude highlighting of search terms. Does not work across HTML tags! So you can't highlight
		// a quote if it (for example) has italic or bold text in it (pfffff)
		echo str_replace(
			$_GET["q"],
			"<span class='selected'>" . filter_var($_GET["q"],FILTER_SANITIZE_SPECIAL_CHARS) . "</span>",
			$html);

		$flat_toc = $this->getFlatTableOfContents($toc);
		for ($i = 0; $i<count($flat_toc); $i++) {
			if ($flat_toc[$i]['id']==$chapter) {
				break;
			}
		}

		$prev = $next = "";
		if ($i>0) {
			$item = $flat_toc[$i-1];
			$prev = "<a href='{$item['link']}' class='book-prev'>{$item['title']}</a>";
		}
		if ($i<count($flat_toc)-1) {
			$item = $flat_toc[$i+1];
			$next = "<a href='{$item['link']}' class='book-next'>{$item['title']}</a>";
		}
		echo "<div class='book-navigation'>$prev$next</div>";
	}

	function displayTableOfContents()
	{
		$chapter = $this->current_chapterId;
		echo $this->renderTableOfContents($chapter, $this->getTableOfContents());
	}

	function renderTableOfContents($chapter, $nav, $level=1) {
		$last = count($nav)-1;

		$html = "<ul class='book-toc level-$level'>";
		foreach ($nav as $i=>$item) {
			$classes = array();
			if ($i==0) {
				$classes[] = "first";
			}
			if ($i==$last) {
				$classes[] = "last";
			}
			if ($item['id'] == $chapter) {
				$classes[] = "current";
			}

			$css = "";
			if (count($classes)>0) {
				$css = " class='" . implode(" ", $classes) . "'";
			}

			$html .= "<li$css>";
			if (strlen($item['heading'])>0) {
				$html .= "<strong>{$item['heading']}</strong>";
			}
			$html .= "<a href='{$item['link']}'>{$item['title']}</a>";
			if (strlen($item['author'])>0) {
				$html .= "<span class='author'>{$item['author']}</span>";
			}
			if (count($item['children'])>0) {
				$html .= renderTableOfContents($chapter, $item['children'], $level+1);
			}
			$html .= "</li>";
		}
		$html .= "</ul>";
		return $html;
	}

	function findChapterIdByNameInUrl($nameInUrl = "")
	{
		$filelist = $this->getFilelist();

		// first, we try to find a file matching the exact path
		$match = null;
		foreach ($filelist as $file) {
			// test if the current file ENDS WITH the (full) filename of the asset
			if (strpos(strrev($file['name']), strrev($nameInUrl))===0) {
				$match = $file;
			}
		}

		// if that doesn't work, try if we can at least find a file with the same filename
		// (I don't understand some of the epub files out there...)
		if (!$match) {
			$best = array('matching'=>0, 'file'=>null);
			$nameInUrl_parts = array_reverse(explode('/', $nameInUrl));
			foreach ($filelist as $file) {
				$file_parts = array_reverse(explode('/', $file['name']));
				$m = 0;
				for ($i=0; $i<min(count($file_parts), count($nameInUrl_parts)); $i++) {
					if ($file_parts[$i]==$nameInUrl_parts[$i]) {
						$m++;
					} else {
						break;
					}
				}
				if ($m>$best['matching']) {
					$best['matching'] = $m;
					$best['file'] = $file;
				}
			}
			if ($best['matching']>0) {
				$match = $best['file'];
			}
		}

		// if we have a match, return the $id
		if ($match) {
			$pi = pathinfo($match['name']);
			@$ext = $pi['extension'];

			switch (strtolower($ext)) {

				case "css":
					// if this is a CSS file, prefix all selectors with our own
					// identifier so that the styling only applies to one part of the page
					$this->renderCSS($match);
					break;

				default:
					return $this->id($match);
			}
		}

		// couldn't serve asset - try to see if this is maybe a book chapter we need to display
		$chapter = $nameInUrl;
		$chapter_parts = explode('/', $chapter);
		$toc = $this->getTableOfContents();
		for ($i=0; $i<count($chapter_parts); $i++) {
			foreach ($toc as $toc_entry) {
				// go through each part of the URL and see if it matches the table of contents
				// in the right position
				if (basename($toc_entry['path'],'/')==basename($chapter_parts[$i])) {
					//$toc = $toc_entry['children'];

					if ($i==count($chapter_parts)-1) {
						// we've found a chapter to display! Render the appropriate page
						// through this byzantine set of calls that I stole from some
						// C5 internals
						//$this->renderText($this_page, $toc_entry['id']);
						$tmpid = $this->id($toc_entry['id']);
						if ($tmpid != "")
						{
							$this->$current_chapterId = $tmpid;
						}
						return $tmpid;
					}
					break;
				}
			}
		}
	}

	public function id($item) {
		if (is_string($item) && preg_match('/[0-9A-Za-z]{32}/', $item)) {
			// this is an MD5 already
			return $item;
		}

		if (array_key_exists('name', $item)) {
			return md5($item['name']);
		}

		if (array_key_exists('src', $item)) {
			return md5($item['src']);
		}

		if (array_key_exists('id', $item)) {
			return md5($item['id']);
		}

		return md5($item);
	}

	public function getTableOfContents($block=null) {
		$overrides = array();
		if ($block) {
			$overrides = $block->getController()->getTocOverrides();
		}

		return $this->processTableOfContents($this->lib->getNavPoints(), $overrides);
	}

	public function getFlatTableOfContents($toc=null, $block=null) {
		if ($toc==null) {
			$toc = $this->getTableOfContents($block);
		}

		$list = array();
		foreach ($toc as $item) {
			$list[] = $item;
			if (count($item['children'])>0) {
				$list = array_merge($list, $this->getFlatTableOfContents($item['children']));
			}
		}
		return $list;
	}

	protected function processTableOfContents($nav, $overrides=array(), $path='') {
		$result = array();
		foreach ($nav as $item) {
			switch ($item['class']) {
				case 'titlepage':
				case 'about':
				case 'part':
				case 'chapter':
				default:
					$this_path = $path . '/' . $this->urlify($item['label']);
					$entry = array(
						'id'      => $this->id($item),
						'heading' => '',
						'title'   => $item['label'],
						'chapter' => '',
						'path'    => $this_path,
						'link'    => $this->base_link . $this_path
					);

					if (array_key_exists($entry['id'], (array)$overrides)) {
						$entry = array_merge($entry, $overrides[$entry['id']]);
					}

					if (count($item['navPoints'])>0) {
						$entry['children'] = $this->processTableOfContents($item['navPoints'], $overrides, $this_path);
					}

					$result[] = $entry;
					break;
			}
		}
		return $result;
	}


	public function urlify($text) {
		// this is a mish-mash of snippets downloaded from the Internet
		// to create a pretty URL from a random piece of text
		//
		// Cannot use the actual URLify function here because we cannot use any C5 classes
		// as we'll be including the whole of C5 at a later point (since this is used in router.php)
		$text = preg_replace(array('/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/'), array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue'), $text);
		$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
		$text = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $text);
		$text = strtolower(trim($text, '-'));
		$text = preg_replace("/[\/_|+ -]+/", '-', $text);

		// if downcode doesn't hit, the char will be stripped here
		$text = preg_replace ('/[^-\w\s]/', '', $text);		// remove unneeded chars
		$text = preg_replace ('/^\s+|\s+$/', '', $text);	// trim leading/trailing spaces
		$text = preg_replace ('/[-\s]+/', '-', $text);		// convert spaces to hyphens
		$text = strtolower ($text);							// convert to lowercase

		return $text;
	}

}