<?php
class Af_WhatIf extends Plugin {
   private $host;

   function about() {
      return array(0.1,
         'Fix what-if?\'s feed links',
         'lotrfan',
         false,
         "http://github.com/lotrfan/ttrss_af_whatif");
   }

   function init($host) {
      // Boilerplate to register hooks.
      $this->host = $host;

      $host->add_hook($host::HOOK_FEED_FETCHED, $this);
   }

   function api_version() {
      return 2;
   }

   function hook_feed_fetched($feed_data, $fetch_url, $owner_uid, $feed) {
      if (strpos($fetch_url, 'what-if.xkcd.') === FALSE) {
         // Not a what-if article
         return $feed_data;
      }

      $doc = new DOMDocument();
      if (@$doc->loadXML($feed_data)) {
         $entries = $doc->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
         foreach ($entries as $entry) {
            $ele_id = $entry->getElementsByTagName('id')->item(0);
            if (!preg_match('/\d+/', $ele_id->nodeValue)) {
               // Need to fix the id
               preg_match('/what-if\.xkcd\.\w+\/(\d+)/i', $entry->getElementsByTagName('summary')->item(0)->nodeValue, $matches);
               if ($matches[1]) {
                  $new_id = $ele_id->nodeValue . "/" . $matches[1];
                  $ele_id->nodeValue = $new_id;
                  $ele_link = $entry->getElementsByTagName('link')->item(0);
                  $ele_link->setAttribute('href', $new_id);
                  //print "new_id: ${new_id}\n";
               }
            }
         }
         return $doc->saveXML();
      }
      return $feed_data;
   }
}
?>
