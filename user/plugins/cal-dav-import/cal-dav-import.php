<?php 

namespace Grav\Plugin;

require_once __DIR__.'/vendor/autoload.php';

use Grav\Common\Grav;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\File\File;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Page\Page;
use Grav\Common\Page\Header;


use ICal\ICal; 

/** 
 * 
 * Class CalDAVImportPlugin 
 * @package Grav\Plugin 
*/
class CalDAVImportPlugin extends Plugin {
    /** 
     * @return array 
     * The getSubscribedEvents() gives the core a list of events 
     * that the plugin wants to listen to. The key of each 
     * array section is the event that the plugin listens to 
     * and the value (in the form of an array) contains the 
     * callable (or function) as well as the priority. The 
     * higher the number the higher the priority. */
    public static function getSubscribedEvents() {
        return [
            'onSchedulerInitialized'    => ['onSchedulerInitialized', 0],
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized() {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $uri = $this->grav['uri'];
        $route = $this->config->get('plugins.cal-dav-import.route');
    }

    /**
     * Run event import as scheduler job
     * Requires Grav 1.6.0 - Scheduler
     */
    public function onSchedulerInitialized(Event $e)
    {
      if ($this->config->get('plugins.cal-dav-import.scheduler.enabled')) {
        $scheduler = $e['scheduler'];
        $at = $this->config->get('plugins.cal-dav-import.scheduler.at');
        $logs = $this->config->get('plugins.cal-dav-import.scheduler.logs');
        $job = $scheduler->addFunction('Grav\Plugin\CalDAVImportPlugin::refreshEvents', [], 'updateEvents');
        $job->at($at);
        $job->output($logs);
        $job->backlink('/plugins/cal-dav-import');
      }
    }

    public static function refreshEvents(){
      $grav = Grav::instance();
      $grav['debugger']->enabled(false);

      static::removeEvents($grav);
      static::importEvents($grav);
    }

    public function needsEventRefresh(){
      $locator = $this->grav['locator'];
      $path = $locator->findResource('user://pages/'.$this->grav['config']->get('plugins.cal-dav-import.folder-name'), true);
                   
      //dir is nearly empty
      if(sizeof(scandir($path)) <= 2){
        return true;
      }

      $importLogFile = $locator->findResource('user://pages/'.$this->grav['config']->get('plugins.cal-dav-import.folder-name'), true) . DS . ".importlog";
      if(file_exists($importLogFile)){
        if(time() - filemtime($importLogFile) >= $this->grav['config']->get('plugins.cal-dav-import.caldav-refresh-interval')*60){
          return true;
        }
      }
      return false;
    }

    public static function removeEvents($grav){
      $path = $grav['locator']->findResource('user://pages/'.$grav['config']->get('plugins.cal-dav-import.folder-name'), true);
      static::rrmdir($path);
    }

    public static function rrmdir($dir) { 
	   if (is_dir($dir)) { 
	     $objects = scandir($dir); 
	     foreach ($objects as $object) { 
	       if ($object != "." && $object != "..") { 
	         if (is_dir($dir."/".$object))
	           static::rrmdir($dir."/".$object);
	         else
	         	if(!(strcasecmp($object, "events.de.md") == 0) && !(strcasecmp($object, ".importlog") == 0)){
	           		unlink($dir."/".$object); 
	         	}
	       } 
       }
       if(static::dir_is_empty($dir)){
        rmdir($dir);
       }
	   } 
   }
   
   /**
   * Check if a directory is empty (a directory with just '.svn' or '.git' is empty)
   *
   * @param string $dirname
   * @return bool
   */
  public static function dir_is_empty($dirname)
  {
    if (!is_dir($dirname)) return false;
    foreach (scandir($dirname) as $file)
    {
      if (!in_array($file, array('.','..','.svn','.git'))) return false;
    }
    return true;
  }

    public static function importEvents($grav) {
        try {
            $ical = new ICal(array(
                'defaultSpan' => 2, // Default value
                'defaultTimeZone' => 'Europe/Berlin',
                'defaultWeekStart' => 'MO', // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter' => null, // Default value
                'filterDaysBefore' => null, // Default value
                'replaceWindowsTimeZoneIds' => false, // Default value
                'skipRecurrence' => false, // Default value
                'useTimeZoneWithRRules' => false, // Default value
            ));
            $ical->initUrl($grav['config']->get('plugins.cal-dav-import.caldav-url'), $grav['config']->get('plugins.cal-dav-import.caldav-user'), $grav['config']->get('plugins.cal-dav-import.caldav-password'));
            
            if($ical->hasEvents()){
                //$events = $ical->events();
                $currentYear = date("Y");
                $today = date("Y-m-d");
                $futureDate = date('Y-m-d', strtotime('+1 year'));
                $events = $ical->eventsFromRange($today. ' 00:00:00', $futureDate.' 23:59:59');
                //$events = $ical->eventsFromRange($currentYear. '-01-01 00:00:00', $currentYear.'-12-31 23:59:59');

                foreach($events as $event) {
                    // Dump every event
                    $cleanedLocation = preg_replace( "/\r|\n/", " ", $event->location);
                    $dtstart = $ical->iCalDateToDateTime($event->dtstart_array[3], false);
                    $dtend = $ical->iCalDateToDateTime($event->dtend_array[3], false);
                    $parsedStartDate = $dtstart->format('d-m-y');
                    $parsedStartDateTime = $dtstart->format('d-m-Y H:i');
                    $parsedEndDateTime = $dtend->format('d-m-Y H:i');
                    $month = "['".$grav['language']->translateArray('MONTHS_OF_THE_YEAR', $dtstart->format('n') - 1, 'de') ." ". $dtstart->format('Y') ."']";
                    $year = $dtstart->format('Y');
                    $categories = static::parseCategories($event);
                    $frequency = "";
                    $description = static::createDescription($event->description);
                    //$eventIcsFile = $event->printData();
                    //$eventIcsFile = substr($this->grav['config']->get('plugins.cal-dav-import.caldav-url'), 0, -7) . "/". $event->uid . ".ics";
                    
                    if(!empty($event->rrule)){
                      $frequencySplitted = explode(";", $event->rrule);
                      if(sizeof($frequencySplitted) > 0){
                       $frequency = strtolower(substr($frequencySplitted[0], strpos($frequencySplitted[0], '=')+1, strlen($frequencySplitted[0])));
                      }
                    }

                    //var_dump($event);
                    $data = <<<EOT
---
title: $event->summary
visible: true
cache_enable: true
#ics: $eventIcsFile
date: $parsedStartDate
rule: $event->rrule
event:
\tstart: $parsedStartDateTime
\tend: $parsedEndDateTime
\tlocation: '$cleanedLocation'
taxonomy:
\tcategory: $categories
\ttag: $month

---
$description


**Veranstaltungsort:** $event->location


EOT;
                    $filename = 'event.de.md';
                    //$dirname = $this->slugify($event->summary);
                    $dirname = $event->uid;

                    /*$pages = $this->grav['pages'];
                    $page = new Page;*/

                    // Get active or default language for page filename
                    // (e.g. 'nl' -> 'default.nl.md')
                    /*$language = Grav::instance()['language']->getLanguage() ? : null;
                    if ($language != '') {
                        $page->name('event.'.$language.'.md');
                    } else {
                        $page->name('event.md');
                    }

                    $locator = $this->grav['locator'];
                    $path = $locator->findResource('user://pages/03.veranstaltungen/', true);

                    $page->filePath($path);*/

                    /*$page - > init(new\ SplFileInfo(__DIR__.
                        '/pages/03.veranstaltungen/'.$dirname.
                        "/".$filename));*/
                    // $page->slug(basename($event->summary));
                    // $pages->addPage($page, $route);

                    // Add text to the content
                    /*$content = $page->content();
                    $page->slug($event->summary);
                    $page->content($content.$event->description);

                    // Add new frontmatter variable and value
                    $header = $page->header();
                    $header = new Header((array) $header);
                    $header->set('title', $event->summary);
                    $header->set('visible', true);
                    $page->header($header->items);
                    //$page - > save();

                    // First page save (required to have an existing new page folder
                    // to store any files with destination '@self' in)
                    $page->save();
                    // Add page to Pages object with routing info
                    
                    $pages->addPage($page, $path);*/

                    $locator = $grav['locator'];
                    $path = $locator->findResource('user://pages/'.$grav['config']->get('plugins.cal-dav-import.folder-name'), true);
                    $dir = $path . DS . $dirname;
                    $fullFileName = $dir. DS . $filename;

                    $file = File::instance($fullFileName);
                    $file->save($data);
                }
                static::writeImportLog($grav, $ical->eventCount);
            }

        } catch (\Exception $e) {
          error_log($e);
        }
    }

    public function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}

public static function parseCategories($event){
  if(empty($event->categories)){
    preg_match_all("/(#\w+)/u", $event->description, $matches, PREG_PATTERN_ORDER);
 
    if(count($matches) > 0){
      return str_replace("#", "", "['".implode("','",$matches[0])."']");
    }
  }
  return $event->categories;
}

public static function createDescription($description){
  if(strpos($description, "#") === false){
    return $description;
  }
  return substr($description, 0, strpos($description, "#"));
}

public static function writeImportLog($grav, $count){
  $data=$count." events successfully imported";
  $locator = $grav['locator'];
                    $path = $locator->findResource('user://pages/'.$grav['config']->get('plugins.cal-dav-import.folder-name'), true);
                    //$dir = $path . DS . $dirname;
                    $fullFileName = $path . DS . ".importlog";
                    file_put_contents($fullFileName, $data);
                    //$file = File::instance($fullFileName);
                    //$file->save($data);
}

}