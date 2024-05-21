<?php
function retrieveHTML($url) {
    $options = array(
        'http' => array(
            'header' => "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
        )
    );
    $context = stream_context_create($options);
    $html = file_get_contents($url, false, $context);
    if ($html === FALSE) {
        die("Error retrieving HTML from the URL: $url");
    }
    return $html;
}

function scrapeArticles($url) {
    $html = retrieveHTML($url);

    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings due to malformed HTML
    if (!$dom->loadHTML($html)) {
        die("Error parsing the HTML from the URL: $url");
    }
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Adjust the XPath query based on the actual HTML structure of the Technology section
    $articles = $xpath->query('//article');
    if ($articles === false || $articles->length == 0) {
        echo "No articles found in the section: $url\n";
        return;
    }

    foreach ($articles as $article) {
        // Extract headline
        $headlineNode = $xpath->query('.//h3', $article)->item(0);
        $headline = $headlineNode ? $headlineNode->nodeValue : 'No headline';

        // Extract link
        $linkNode = $xpath->query('.//a', $article)->item(0);
        $link = $linkNode ? $linkNode->getAttribute('href') : 'No link';
        // Prepend BBC domain if the link is relative
        if (strpos($link, 'http') === false) {
            $link = 'https://www.bbc.com' . $link;
        }

        // Extract content/summary
        $contentNode = $xpath->query('.//p', $article)->item(0);
        $content = $contentNode ? $contentNode->nodeValue : 'No content';

        // Extract image URL
        $imageNode = $xpath->query('.//img', $article)->item(0);
        $image = $imageNode ? $imageNode->getAttribute('src') : 'No image';

        echo "Headline: " . $headline . "<br>";
        echo "Link: <a href='" . $link . "'>" . $link . "</a><br>";
        echo "Content: " . $content . "<br>";
        if ($image !== 'No image') {
            echo "<img src='" . $image . "' alt='Image' style='max-width: 200px;'><br>";
        } else {
            echo "No image<br>";
        }
        echo "----------------------<br>";
    }
}

// URL of the Technology news section
$url = 'https://www.bbc.com/news/technology';

echo "<h2>Scraping Technology Section</h2>";
scrapeArticles($url);
echo "<hr>";
?>
