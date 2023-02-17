<?php
    /*
     * $Id: mime.php 1577 2011-10-31 17:56:46Z mortonda@dgrmm.net $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
     *                   David Morton   <mortonda@dgrmm.net>
     * All rights reserved.
     *
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */

   require_once ("core.php");
   require_once ("maia_db.php");
   require_once ("Mail/mimeDecode.php");  // PEAR::Mail::mimeDecode.php
   // edit jjs 2020-03-26
   //  back out edit, path platform dependent jfr 02/16/2023
   require_once 'HTMLPurifier.auto.php';
   //require_once ("/var/htmlpurifier/library/HTMLPurifier.auto.php");


   /*
    * display_parts(): Recursively decode and display the contents of
    *                  a MIME part.
    */
    
   
   function display_parts($structure)
   {
      global $lang;
      global $smarty;

      $primary = strtolower(trim($structure->ctype_primary));
      $secondary = strtolower(trim($structure->ctype_secondary));
      $ctype = $primary . "/" . $secondary;
      $messagepart = "";

      $message_charset = get_charset($structure);

      switch ($primary) {

         case "multipart":
	    /* function requires array, caste to array */
            /* if (!array_key_exists('parts', $structure)) { */
	    if (!array_key_exists('parts', (array)$structure)) {
                  $ret = "[" . $lang['text_invalid'] . "]<br>";
                  break;
            }
            // Recursively decode each of the sub-parts of this
            foreach ($structure->parts as $part) {
               $messagepart .= display_parts($part);

            }
            $smarty->assign("messagepart", $messagepart);
            $smarty->assign("contenttype", $ctype);

            $ret = $smarty->fetch("view-message.tpl");
            break;

         case "text":

            switch ($secondary) {

               // Simple text, just word-wrap it to keep it to
               // a sane width.
               case "plain":

                  $messagepart = "<pre>" . sanitize_html(wordwrap(iconv($message_charset, 'utf-8',  $structure->body), 70)) . "</pre>";
                  $smarty->assign("messagepart", $messagepart);
                  $smarty->assign("contenttype", $ctype);

                  $ret = $smarty->fetch("view-message.tpl");
            	  break;

               // HTML content, clean it up a bit and display it.
               case "html":

                  $messagepart  = sanitize_html(iconv($message_charset, 'utf-8',  $structure->body));
                  $smarty->assign("messagepart", $messagepart);
                  $smarty->assign("contenttype", $ctype);

                  $ret = $smarty->fetch("view-message.tpl");
            	  break;

               // Some other odd text format we don't support, ignore it.
               default:

              $ret = "[" . $lang['text_unsupported'] . ": " . $ctype . "]<br>";

            }
            break;

         default:

            // An unsupported content type, ignore it.
            $ret = "[" . $lang['text_unsupported'] . ": " . $ctype . "]<br>";

      }
	return $ret;
   }


   class MaiaDisplayLinkURI extends HTMLPurifier_Injector
   {

       public $name = 'DisplayLinkURI';
       public $needed = array('a');

       private $idcount = 1;

       public function handleElement(&$token) {
       }

       public function handleEnd(&$token) {
           if (isset($token->start->attr['href'])){
               $url =  MaiaDisplayLinkURI::pretty_url($token->start->attr['href']);
               unset($token->start->attr['href']);
               $token->start->attr['class'] = 'DisplayLink';
               $token->start->attr['id'] = 'DisplayLink_' . $this->idcount;

               $token = array_merge(array($token,
                       new HTMLPurifier_Token_Start('span', array('class' => 'DisplayLinkURL', 'id'=>'tip_DisplayLink_' . $this->idcount))),
                       $url,
                       array(
                       new HTMLPurifier_Token_End('span')
                       ));
               $this->idcount += 1;
           } else {
               // nothing to display
           }
       }

       private static function color_tokens($part, $class) {
           $token = array(
               new HTMLPurifier_Token_Start('font', array('class' => "DisplayLink_" . $class)),
                  new HTMLPurifier_Token_Text($part),
                  new HTMLPurifier_Token_End('font')
               );
            return $token;
       }

       private static function pretty_url($url) {
         // Make sure we have a string to work with
         if(!empty($url)) {
           // Explode into URL keys
           $urllist=parse_url($url);

           // Make sure we have a valid result set and a query field
           if(is_array($urllist) ) {
           // Build the the final output URL
             $newurl=array();
             if (isset($urllist["scheme"]))   {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(($urllist['scheme'] . "://"),"scheme")); }
             if (isset($urllist["user"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["user"] . ":", "user")); }
             if (isset($urllist["pass"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["pass"] . "@", "pass")); }
             if (isset($urllist["host"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["host"], "host")); }
             if (isset($urllist["port"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(":" . $urllist["port"], "port")); }
             if (isset($urllist["path"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["path"], "path")); }
             if (isset($urllist["query"]))    {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(("?" . $urllist["query"]), "query")); }
             if (isset($urllist["fragment"])) {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens("#" . $urllist["fragment"], "fragment")); }
             return $newurl;
           }
         }
         return array();
       }
   }


   /*
    * sanitize_html(): Do some trivial filtering of HTML contents to
    *                  render it suitable for displaying in a table
    *                  cell (rather than as a complete page).
    */
   function sanitize_html($body)
   {
       global $purifier_cache;
       if (!isset($purifier_cache)) {
           $purifier_cache = null;
       }

       $config = HTMLPurifier_Config::createDefault();
       if ($purifier_cache) {
           $config->set('Cache.SerializerPath', $purifier_cache);
       } else {
           $config->set('Cache.DefinitionImpl', null);
       }
       $config->set('URI.Disable', true);
       $config->set('Attr.EnableID', true);
       $config->set('AutoFormat.Custom', array(new MaiaDisplayLinkURI));
       $purifier = new HTMLPurifier($config);

       $html =  $purifier->purify($body);

       return ($html);
   }

    /*
      last_ditch_mime_decode() is a last attempt to decode mime headers that
      iconv_mime_decode missed, presumably due to an unknown charset.  If a
      default encoding from the email is provided, we'll try it.  Only do
      any changes, though, if a mime patern was found, otherwise there are no
      changes needed.
    */
    function last_ditch_mime_decode($str, $encoding = "ISO-8859-1") {
      $found_encoding = false;
      while (preg_match( "/(=\?.*\?([BQ])\?(.+)\?=)/" ,$str, $matches)) {
        $found_encoding = true;
        if ($matches[2] == 'B') {
          $str = str_replace ( $matches[1] , base64_decode($matches[3]) , $str);
        } elseif($matches[1] == 'Q') {
          $str = str_replace ( $matches[1] , quoted_printable_decode($matches[3]) , $str);
        }
      }
      return $found_encoding ? iconv($encoding, 'utf-8', $str) : $str;
    }


    /* get_charset($structure)   Given a mime document structure, find the charset encoding,
      or default to iso-8859-1
    */
    function get_charset($structure) {
      if ($structure->ctype_parameters && array_key_exists('charset', $structure->ctype_parameters )) {
        return $structure->ctype_parameters['charset'];
      } else {
        return "iso-8859-1"; //arbitrary default charset...
      }
    }
?>
