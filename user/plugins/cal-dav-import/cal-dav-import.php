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

        if ($route && $route == $uri->path()) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0]
            ]);
        }

        // Enable the main event we are interested in
        //$this->enable(['onPageContentRaw' => ['onPageContentRaw', 0]]);
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageContentRaw(Event $e) {
        // Get a variable from the plugin configuration
        //$text = $this->grav['config']->get('plugins.cal-dav-import.caldav-url');

        // Get the current raw content
        //$content = $e['page']->getRawContent();

        // Prepend the output with the custom text and set back on the page
        //$e['page']->setRawContent($text . "\n\n" . $content);
    }

    /**
    * Send user to a random page
    */
    public function onPageInitialized()
    {
        // Initialize plugin
        if($this->needsEventRefresh()){
        	$this->removeEvents();
          	$this->importEvents();
        }
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
        //if(strtotime(filemtime($importLogFile)) >= strtotime("-".$this->grav['config']->get('plugins.cal-dav-import.caldav-refresh-interval')." minutes")){
        if(time() - filemtime($importLogFile) >= $this->grav['config']->get('plugins.cal-dav-import.caldav-refresh-interval')*60){
          //var_dump("too old");
          return true;
        }
      }
      return false;
    }

    public function removeEvents(){
      $path = $this->grav['locator']->findResource('user://pages/'.$this->grav['config']->get('plugins.cal-dav-import.folder-name'), true);
      //var_dump($path);
      $this->rrmdir($path);
    }

    public function rrmdir($dir) { 
	   if (is_dir($dir)) { 
	     $objects = scandir($dir); 
	     //var_dump($objects);
	     foreach ($objects as $object) { 
	       if ($object != "." && $object != "..") { 
	         if (is_dir($dir."/".$object))
	           $this->rrmdir($dir."/".$object);
	         else
	         	if(!(strcasecmp($object, "events.de.md") == 0) && !(strcasecmp($object, ".importlog") == 0)){
	           		unlink($dir."/".$object); 
	         	}
	       } 
       }
       if($this->dir_is_empty($dir)){
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
  public function dir_is_empty($dirname)
  {
    if (!is_dir($dirname)) return false;
    foreach (scandir($dirname) as $file)
    {
      if (!in_array($file, array('.','..','.svn','.git'))) return false;
    }
    return true;
  }

    public function importEvents() {
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
            $ical->initUrl($this->grav['config']->get('plugins.cal-dav-import.caldav-url'), $this->grav['config']->get('plugins.cal-dav-import.caldav-user'), $this->grav['config']->get('plugins.cal-dav-import.caldav-password'));
            
            if($ical->hasEvents()){
                //$events = $ical->events();
                $currentYear = date("Y");
                $events = $ical->eventsFromRange($currentYear.'-01-01 00:00:00', $currentYear.'-12-31 23:59:59');

                foreach($events as $event) {
                    // Dump every event
                    $cleanedLocation = preg_replace( "/\r|\n/", " ", $event->location);
                    $dtstart = $ical->iCalDateToDateTime($event->dtstart_array[3], false);
                    $dtend = $ical->iCalDateToDateTime($event->dtend_array[3], false);
                    $parsedStartDate = $dtstart->format('d-m-y');
                    $parsedStartDateTime = $dtstart->format('d-m-Y H:i');
                    $parsedEndDateTime = $dtend->format('d-m-Y H:i');
                    $month = $this->grav['language']->translateArray('MONTHS_OF_THE_YEAR', $dtstart->format('n') - 1, 'de');
                    $year = $dtstart->format('Y');
                    $category = $event->categories;
                    $frequency = "";
                    //$eventIcsFile = $event->printData();
                    //$eventIcsFile = substr($this->grav['config']->get('plugins.cal-dav-import.caldav-url'), 0, -7) . "/". $event->uid . ".ics";

                    /*if (strpos($event->summary, 'gottesdienst')) {
                      $category = "Gottesdienst";
                    }*/

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
ics: $eventIcsFile
date: $parsedStartDate
rule: $event->rrule
event:
\tstart: $parsedStartDateTime
\tend: $parsedEndDateTime
\tlocation: '$cleanedLocation'
taxonomy:
\tcategory: $category
\ttag: 

---
$event->description


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

                    $locator = $this->grav['locator'];
                    $path = $locator->findResource('user://pages/'.$this->grav['config']->get('plugins.cal-dav-import.folder-name'), true);
                    $dir = $path . DS . $dirname;
                    $fullFileName = $dir. DS . $filename;

                    $file = File::instance($fullFileName);
                    $file->save($data);
                }
                $this->writeImportLog($ical->eventCount);
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

public function writeImportLog($count){
  $data=$count." events successfully imported";
  $locator = $this->grav['locator'];
                    $path = $locator->findResource('user://pages/'.$this->grav['config']->get('plugins.cal-dav-import.folder-name'), true);
                    //$dir = $path . DS . $dirname;
                    $fullFileName = $path . DS . ".importlog";
                    file_put_contents($fullFileName, $data);
                    //$file = File::instance($fullFileName);
                    //$file->save($data);
}

}