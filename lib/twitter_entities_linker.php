<?php
/**
 * Modified from https://github.com/ogaoga/TwitterEntitiesLinker - Bit of a dead project, but seems to work well
 *
 * Twitter Entities Linker class.
 *
 * PHP versions 5
 *
 * Copyright 2010, ogaoga.org
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2010, ogaoga.org
 * @link          http://www.ogaoga.org/
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Twitter Entities Linker class
 *
 * usage:  TwitterEntitiesLinker::getHtml($tweet);
 *
 */
class TwitterEntitiesLinker {

  /**
   * get html source
   *
   * @param  $tweet : a json decoded tweet object
   * @param  $highlight : set params if you want to highlight any text
   *                      (optional)
   *
   * $highlight should be set with preg_replace params like the following:
   *
   *    $highlight = array('patterns'
   *                         => array('/notice/i',
   *                                  '/caution/i',
   *                                  ...),
   *                       'replacements'
   *                         => array('<span class="notice">$0</span>',
   *                                  '<b>$0</b>',
   *                                  ...)
   *                       );
   *
   * @return html source
   */
  public static function getHtml($tweet, $highlight = array()) {
    $convertedEntities = array();

    // check entities data exists
    if ( ! isset($tweet->entities) ) {
      return $tweet->text;
    }

    // make entities array
    foreach ( $tweet->entities as $type => $entities ) {
      foreach ( $entities as $entity ) {
        $entity->type = $type;
        $convertedEntities[] = $entity;
      }
    }

    // sort entities
    usort($convertedEntities,
          "TwitterEntitiesLinker::sortFunction");

    // split entities and texts
    $pos = 0;
    $entities = array();
    foreach ($convertedEntities as $entity) {
      // not entity
      if ( $pos < $entity->indices[0] ) {
        $substring = mb_substr($tweet->text,
                               $pos,
                               $entity->indices[0] - $pos,
                               'utf-8');
        $entities[] = array('text' => $substring, 
                            'data' => null);
        $pos = $entity->indices[0];
      }
      // entity
      $substring = mb_substr($tweet->text,
                             $pos,
                             $entity->indices[1] - $entity->indices[0],
                             'utf-8');
      $entities[] = array('text' => $substring, 
                          'data' => $entity);
      $pos = $entity->indices[1];
    }
    // tail of not entity
    $length = mb_strlen($tweet->text, 'utf-8');
    if ( $pos < $length ) {
      $substring = mb_substr($tweet->text,
                             $pos,
                             $length - $pos,
                             'utf-8');
      $entities[] = array('text' => $substring, 
                          'data' => null);
    }

    // replace
    $html = "";
    foreach ( $entities as $entity ) {
      if ( $entity['data'] ) {
        if ( $entity['data']->type == 'urls' ) {
          $URL = $entity['data']->url;
          $displayURL = $entity['data']->display_url;
          $html .= '<a href="'.$URL.'" target="_blank" rel="nofollow" class="twitter-timeline-link">'.self::highlightText($displayURL, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'hashtags' ) {
          $text = $entity['data']->text;
          $html .= '<a href="http://twitter.com/hashtag/'.$text.'?src=hash" title="#'.$text.'" class="twitter-hashtag" rel="nofollow">#'.self::highlightText($text, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'symbols' ) {
          $text = $entity['data']->text;
          $html .= '<a href="http://twitter.com/search?q='.$text.'&src=ctag" title="$'.$text.'" class="twitter-symboltag" rel="nofollow">$'.self::highlightText($text, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'user_mentions' ) {
          $screen_name = $entity['data']->screen_name;
          $html .= '<a class="twitter-atreply" data-screen-name="'.$screen_name.'" href="http://twitter.com/'.$screen_name.'" rel="nofollow">@'.self::highlightText($screen_name, $highlight).'</a>';
        }
        else if ( $entity['data']->type == 'media' ) {
          $URL = $entity['data']->url;
          $displayURL = $entity['data']->display_url;
          $html .= '<a href="'.$URL.'" target="_blank" rel="nofollow" class="twitter-media-link">'.self::highlightText($displayURL, $highlight).'</a>';
        }
        else {
        }
      }
      else {
        $html .= self::highlightText($entity['text'], $highlight);
      }
    }
    // return 
    return $html;
  }

  /**
   * sort function
   *
   * @param   data a
   * @param   data b
   * @return  1 or -1 or 0
   */
  static private function sortFunction($a, $b)  {
    if ($a->indices > $b->indices) { return 1; }
    else if ($a->indices < $b->indices) { return -1; }
    else { return 0; }
  }

  /**
   * highlight text
   */
  static private function highlightText($text, $highlight) {
    if ( $highlight ) {
      $text = preg_replace($highlight['patterns'],
                           $highlight['replacements'],
                           $text);
    }
    return $text;
  }
}
?>
