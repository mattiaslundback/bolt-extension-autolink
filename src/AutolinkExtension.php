<?php

// Autolink Extension for Bolt, by Mattias Lundback

namespace Bolt\Extension\MattiasLundback\Autolink;

use Bolt\Extension\SimpleExtension;
use Maid\Maid;


class AutolinkExtension extends SimpleExtension

{
    protected function registerTwigFunctions()
    {
        return [ 'autolink' => 'autolink' ,
                 'autolinkitem' => 'autolinkitem',
                ];
    }
    
    protected function getDefaultConfig()
    {
        return [
            'config' => [
                'css' => false,
                'thumbapi' => 'https://api.thumbalizr.com/?url=%url%&width=300',
                'google_cse_url' => 'https://www.googleapis.com/customsearch/v1?key=%key%&cx=%cse%&q=%search%&alt=atom',
            ]
        ];
    }

    public function autolink($cse = false, $title = false) {
        
      function cleanurl($url)
        {
          if ( substr($url, 0, 7) == 'http://' ) {
          $url = substr($url, 7);
        }
        if ( substr($url, 0, 8) == 'https://' ) {
          $url = substr($url, 8);
        }
        if ( substr($url, 0, 4) == 'www.') {
          $url = substr($url, 4);
        }
        if ( strpos($url, '/') !== false ) {
          $ex = explode('/', $url);
          $url = $ex['0'];
        } 
        return $url;
      }
      
      function cleantitle($url)
      {
        if ( strpos($url, '|') !== false ) {
          $ex = explode('|', $url);
          $url = $ex['0'];
        } 
        if ( strpos($url, ' -') !== false ) {
          $ex = explode('-', $url);
          $url = $ex['0'];
        } 
        if ( strpos($url, ' -') !== false ) {
          $ex = explode('-', $url);
          $url = $ex['0'];
        } 
        $url = wordwrap($url,15,"<br>\n");
        return $url;
      }
      
      function hamtabild($url = false, $thumbapi = false)
      {
        // Construct a cache handle from the URL
        $handle = preg_replace('/[^A-Za-z0-9]+/', '', $url);
        $handle = str_replace('httpwww', '', $handle);
        $cachedir = 'extensions/autolink';
        $cachefile = $cachedir . '/' . $handle . '.jpg';
        
        if (!file_exists($cachefile) OR rand(1,200) == 1) {
          $thumbapi = str_replace('%url%', $url, $thumbapi);
          $file = file_get_contents($thumbapi);
          file_put_contents($cachefile, $file);
          $url = $cachefile;
          if (!file_exists($cachefile)) {
            $url = 'extensions/autolink/01.jpg';
          }
        }
        return $url;
      }
      
      // Main
      
      $config = $this->getConfig();
      $url = $config['config']['google_cse_url'];
     
      include_once 'simple_html_dom.php';

      $title = str_replace(' ', '+', $title);  
      $title = str_replace("?", "", $title); 
      $title = str_replace("!", "", $title);
      $title = str_replace("-", "", $title);
      $title = str_replace("(", "", $title);
      $title = str_replace(")", "", $title);
      $title = str_replace(",", "", $title);
      $url = str_replace("%search%", $title, $url);  
      $url = str_replace("%cse%", $cse, $url);
        
      // Construct a cache handle from the URL
      $handle = preg_replace('/[^A-Za-z0-9]+/', '', $title);
      $handle = str_replace('httpwww', '', $handle);
      $cachedir = 'extensions/autolink';
      $cachefile = $cachedir . '/' . $handle . '.html';
      
      $cache = array(); 
        
      // Create cache directory if it does not exist
      if (!file_exists($cachedir)) {
        mkdir($cachedir, 0777, true);
        }
    
      if (file_exists($cachefile)) {
           $articles = unserialize(file_get_contents($cachefile));
      }
        
      if (!file_exists($cachefile) OR rand(1,1000) == 1) {
            
            $html = file_get_html($url);
            
            if (is_object($html)) {

            foreach ($html->find('entry') as $entry) {
              $item['id'] = $entry->find('id', 0)->plaintext;
              $item['title'] = $entry->find('title', 0)->plaintext;
              $item['summary'] = $entry->find('summary', 0)->plaintext;
              $item['organisationURL'] = $entry->find("cse:PageMap cse:DataObject[type='organisation'] cseAttribute[name='url']", 0)->value;
              $item['organisationLOGO'] = $entry->find("cse:PageMap cse:DataObject[type='organisation'] cseAttribute[name='logo']", 0)->value;
              $item['author'] = cleanurl($item['id']);
              $item['title'] = cleantitle($item['title']);
              $thumbapi = $config['config']['thumbapi'];
              $aa = hamtabild($item['author'], $thumbapi);
              $item['tumnagel'] = $aa;
              $articles[] = $item;
            }
            }
            $html->clear();
            unset($html);
            file_put_contents($cachefile, serialize($articles));
        } 
        
    } 
        
    public function autolinkitem($title = false, $falttyp = false, $nummer = false) {
      
        $config = $this->getConfig();
        $url = $config['config']['google_cse_url'];
     
        $title = str_replace(' ', '+', $title);  
        $title = str_replace("?", "", $title); 
        $title = str_replace("!", "", $title);
        $title = str_replace("-", "", $title);
        $title = str_replace("(", "", $title);
        $title = str_replace(")", "", $title);
        $title = str_replace(",", "", $title);
        $articles = unserialize(file_get_contents($cachefile));
        $url = str_replace("%search%", $title, $url);  
        
         // Construct a cache handle from the URL
        $handle = preg_replace('/[^A-Za-z0-9]+/', '', $title);
        $handle = str_replace('httpwww', '', $handle);
        $cachedir = 'extensions/autolink';
        $cachefile = $cachedir . '/' . $handle . '.html';
        
        $articles = unserialize(file_get_contents($cachefile));
        $article = $articles[$nummer];
        $falt = $article[$falttyp];
        
        // Completely remove style and script blocks
        
        $falt = html_entity_decode($falt);
        $falt = strip_tags($falt, '');
        $falt = str_replace("...", "", $falt);
        $falt = trim($falt);
        
        return new \Twig_Markup($falt, 'UTF-8');
        
    }
}
