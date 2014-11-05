<?php
// Autolink Extension for Bolt, by Mattias Lundback

namespace Autolink;

class Extension extends \Bolt\BaseExtension
{
    const NAME = 'autolink';
    public function getName()
    {
        return Extension::NAME;
    }
    /**
     * Info block for Autolink Extension.
     */
    function info()
    {

        $data = array(
            'name' => "Autolink",
            'description' => "Inserts links to relevant content on selected websites. Uses Google Custom Search API.",
            'keywords' => "bolt, automatic link, Google, aggregator",
            'author' => "Mattias Lundback",
            'link' => "http://github.com/sekl/Autolink",
            'version' => "1.0",
            'required_bolt_version' => "1.0.2",
            'highest_bolt_version' => "1.1.4",
            'type' => "General",
            'first_releasedate' => "2014-10-31",
            'latest_releasedate' => "2014-10-31",
            'dependencies' => "",
            'priority' => 10
        );

        return $data;

    }

    /**
     * Initialize Autolink. Called during bootstrap phase.
     */
    function init()
    {

        // If yourextension has a 'config.yml', it is automatically loaded.
        // $foo = $this->config['bar'];

        // Add CSS file
         $this->addCSS("assets/autolink.css");

        // Initialize the Twig function
        $this->addTwigFunction('Autolink', 'twigAutolink_aggregator');
        $this->addTwigFunction('AutolinkRSS', 'twigAutolink_RSS');

    }
    
    
    
     /**
     * Twig function {{ twigAutolink_RSS() }} in Autolink extension.
     */
    function twigAutolink_RSS($url = false, $title = false)
    {
    
        
        if ($title!="") {
            $title = htmlspecialchars($title, ENT_QUOTES, "UTF-8") . "';\n";
        } else {
            $title = "";
        }
    
        $title = str_replace(' ', '+', $title);  
        $title = str_replace("?", "", $title); 
        $title = str_replace("!", "", $title);
        $title = str_replace("-", "", $title);
        $title = str_replace("(", "", $title);
        $title = str_replace(")", "", $title);
        $title = str_replace(",", "", $title);
        $url = str_replace("%search%", $title, $url);  
      
   
        
        if(!$url) {
            return new \Twig_Markup('External feed could not be loaded! No URL specified.', 'UTF-8'); 
        }

        // Construct a cache handle from the URL
        $handle = preg_replace('/[^A-Za-z0-9_-]+/', '', $url);
        $handle = str_replace('httpwww', '', $handle);
        $cachedir = $this->basepath . '/cache/';
        $cachefile = $cachedir . '/' . $handle.'.cache';

          // Use cache file if possible
        if (!file_exists($cachefile)) {
        	
            return new \Twig_Markup('No_links', 'UTF-8');
        }
        
        if(file_exists($cachefile)) {
        	$innehall = file_get_contents($cachefile);
            return new \Twig_Markup($innehall, 'UTF-8'); 
        }
    }








    /**
     * Twig function {{ twigAutolink_aggregator() }} in Autolink extension.
     */
    function twigAutolink_aggregator($url = false, $title = false, $options = array())
    {
    
        
        if ($title!="") {
            $title = htmlspecialchars($title, ENT_QUOTES, "UTF-8") . "';\n";
        } else {
            $title = "";
        }
    
        $title = str_replace(' ', '+', $title);  
        $title = str_replace("?", "", $title); 
        $title = str_replace("!", "", $title);
        $title = str_replace("-", "", $title);
        $title = str_replace("(", "", $title);
        $title = str_replace(")", "", $title);
        $title = str_replace(",", "", $title);
        $url = str_replace("%search%", $title, $url);  
   
        
        if(!$url) {
            return new \Twig_Markup('External feed could not be loaded! No URL specified.', 'UTF-8'); 
        }

        // Construct a cache handle from the URL
        $handle = preg_replace('/[^A-Za-z0-9_-]+/', '', $url);
        $handle = str_replace('httpwww', '', $handle);
        $cachedir = $this->basepath . '/cache/';
        $cachefile = $cachedir . '/' . $handle.'.cache';

        // default options
        $defaultLimit = 10;
        $defaultShowDesc = false;
        $defaultShowDate = false;
        $defaultDescCutoff = 100;

        // Handle options parameter

        if(!array_key_exists('limit', $options)) {
            $options['limit'] = $defaultLimit;
        }
        if(!array_key_exists('showDesc', $options)) {
            $options['showDesc'] = $defaultShowDesc;
        }
        if(!array_key_exists('showDate', $options)) {
            $options['showDate'] = $defaultShowDate;
        }
        if(!array_key_exists('descCutoff', $options)) {
            $options['descCutoff'] = $defaultDescCutoff;
        }

        // Create cache directory if it does not exist
        if (!file_exists($cachedir)) {
            mkdir($cachedir, 0777, true);
        }
        

        // Use cache file if possible
        if (file_exists($cachefile)) {
            return new \Twig_Markup(file_get_contents($cachefile), 'UTF-8');
        }

        // Make sure we are sending a user agent header with the request
        $streamOpts = array(
            'http' => array(
                'user_agent' => 'libxml',
            )
        );

         libxml_set_streams_context(stream_context_create($streamOpts));
         
         $doc = new\DOMDocument();


        // Load feed and suppress errors to avoid a failing external URL taking down our whole site
        if (!@$doc->load($url)) {
            return new \Twig_Markup('No links found by Autolink yet.', 'UTF-8');
        }

        // Parse document
        $feed = array();

        foreach($doc->getElementsByTagName('entry') as $node) {
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'id' => $node->getElementsByTagName('id')->item(0)->nodeValue,
            );
            array_push($feed, $item);
        }

        $items = array();

        // if limit is set higher than the actual amount of items in the feed, adjust limit
        $limit = $options['limit'] > count($feed) ? count($feed) : $options['limit'];

        for($i = 0; $i < $limit; $i++) {
        	    $title = htmlentities(strip_tags($feed[$i]['title']), ENT_COMPAT, "UTF-8");
        	    $title = str_replace("&quot;", "", $title);
        	    $title = str_replace("&amp;#39;", "", $title);
        	    $title = str_replace("&amp;quot;", "", $title);
                $link = htmlentities(strip_tags($feed[$i]['id']), ENT_COMPAT, "UTF-8");
                array_push($items, array(
                    'title' => $title,
                    'id'  => $link,
                ));
        }

        $html = '<div class="autolink">' . '     :::     ';

        foreach ($items as $item) {
            $html .= '<a target="_blank" href="' . $item['id'] . '" class="autolink-title" >' . $item['title'] . '</a>' . '     :::     ';
        }

        $html .= '</div>';

        // create or refresh cache file
        file_put_contents($cachefile, $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
