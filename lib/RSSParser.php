<?php
class RSSParser {
    private $url;
    private $items = [];
    
    public function __construct($url) {
        $this->url = $url;
        $this->parse();
    }
    
    private function parse() {
        $xml = @simplexml_load_file($this->url);
        if ($xml === false) {
            throw new Exception("Failed to load RSS feed");
        }
        
        foreach ($xml->channel->item as $item) {
            $this->items[] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => (string)$item->description,
                'pubDate' => (string)$item->pubDate
            ];
        }
    }
    
    public function getItems() {
        return $this->items;
    }
}
?>