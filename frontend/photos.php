<?php
  include_once("includes/start.php");
  $title = 'Camp Photos';
  $tpl->set('title', $title);

  $tpl->set('unapproved', false, true);

  # Figure out the image directory (for phpThumb to use)
  array_pop($script);
  $tpl->set('imageURL', implode("/", $script) . "photos");

  # Check that the 'empty' variable is valid
  if ($SEGMENTS[1] != "true" && $SEGMENTS[1] != "false" && $SEGMENTS[1] != "admin" && $SEGMENTS[1]) {
    header("Location: /photos");
  }
  if ($SEGMENTS[1] == "admin" && $user->isCamper()) {
    header("Location: /photos");
  }

  # Calculate the number of unapproved captions
  if ($user->isLeader()) {
    $query = "SELECT count(*) FROM `photo_captions` WHERE `Status` = 0";
    $result = do_query($query);
    $row = fetch_row($result);
    if ($row[0] > 0) {
      if ($row[0] == 1) {
        $suffix = "";
      } else {
        $suffix = "s";
      }
      $tpl->set('unapproved', true);
      $tpl->set('number', $row[0]);
      $tpl->set('suffix', $suffix);
    } else {
      if ($SEGMENTS[1] == "admin") {
        header("Location: /photos");
      }
    }
  }

  if (!isset($_GET['thumb']) || !is_numeric($_GET['thumb'])) {
    $thumbnailLimit = 0;
  } else {
    $thumbnailLimit = $_GET['thumb'];
  }
  $thumbnailsGenerated = 0;
  $thumbnailError = false;

    # Get an array of images that still have captions pending approval
    $pendingCaptions = array();

    $query = "SELECT COUNT(*) AS `Count`, `Filename` FROM `photo_captions` WHERE `Status` = 0 GROUP BY `Filename`";
    $result = do_query($query);
    while ($row = fetch_row($result)) {
    $pendingCaptions[$row['Filename']] = $row['Count'];
  }

  # Get an array of the latest caption for each image
  $latestCaptions = array();

  $query = "SELECT * FROM `photo_captions` WHERE `Status` ";
  if ($user->isLeader()) {
    $query .= "!= -1";
  } else {
    $query .= "= 1";
  }
  $query .= " ORDER BY `Filename` ASC, `ID` DESC";
  $result = do_query($query);
  while ($row = fetch_row($result)) {
    if (!isset($latestCaptions[$row['Filename']])) {
      $latestCaptions[$row['Filename']] = $row['Caption'];
    }
  }
  $filter = false;
  if (isset($_GET['username']) && isset($people[$_GET['username']])) {
    $filter = "Currently showing photos of ".userpage($_GET['username'], true).":";
    $imageFilter = array();
    $query = "SELECT `Filename` FROM `photo_tags` WHERE `Username` = '{$_GET['username']}'";
    $result = do_query($query);
    while ($row = fetch_row($result)) {
      $imageFilter[] = $row['Filename'];
    }
  }
  $tpl->set("filter", $filter, true);

  # Generate a list of photos.
  $pictures = array();
  if ($dh = opendir("camp-data/photos")) {
    while (($file = readdir($dh)) !== false) {
      if (isset($imageFilter) && array_search($file, $imageFilter) === false) {
        continue;
      }

      if (stristr($file, "png") || stristr($file, "gif") || stristr($file, "jpg")) {
        $thumbnail = generate_thumbnail("camp-data/photos/$file", 200, 133);
        if (!$thumbnail) {
          $thumbnail = false;
          $thumbnailError = true;
        }

        if (isset($temp)) {
          unset($temp);
        }
        $class = "img";
        # If it hasn't been approved, change the class.
        if ($user->isLeader() && isset($pendingCaptions[$file])) {
          $class = "not";
        }
        if (isset($latestCaptions[$file])) {
          # There is a caption.
          $caption = $latestCaptions[$file];
          if ($SEGMENTS[1] != "true" || !$SEGMENTS[1]) {
            if (($SEGMENTS[1] == "admin" && $class == "not") or ($SEGMENTS[1] != "admin")) {
              if (strlen($caption) > 150) {
                $caption = substr($caption, 0, 150) . "...";
              }
              $temp = array("filename" => $file, "caption" => $caption, "class" => $class);
            }
          }
        } else {
          # There is no caption.
          if ($SEGMENTS[1] == "true" || !$SEGMENTS[1]) {
            $temp = array("filename" => $file, "caption" => "&nbsp;", "class" => $class);
          }
        }
        if (isset($temp)) {
          if (!$thumbnail) {
            $temp['imageURL'] = "/resources/img/thumbnail-unavailable.png";
          } else {
            $temp['imageURL'] = "/camp-data/photos/cache/$thumbnail";
          }
          $temp['uber'] = uberButton(false, "/view-photo/" . $temp["filename"]);
          $pictures[] = $temp;
        }
      }
    }
    closedir($dh);
  }

  $nop = false;
  $catFull = true;

  if (count($pictures) == 0) {
    if ($SEGMENTS[1]) {
      $catFull = false;
    } else {
      $nop = true;
    }
  }

  if ($thumbnailError) {
    $warning = "Some thumbnails are currently unavailable. Due to a bug, only leaders are currently able to generate thumbnails. We apologise for the inconvenience.";
    if ($user->isLeader()) {
      $warning .= "<br /><span style='font-size: 70%;'>Leader use only: generate <a href='?thumb=10'>10</a> | <a href='?thumb=15'>15</a> | <a href='?thumb=20'>20</a> | <a href='?thumb=30'>30</a> | <a href='?thumb=50'>50</a> | <a href='?thumb=-1'>all</a> thumbnails. Higher numbers will use a lot of CPU power so only do it in \"off-peak\" times!</span>";
    }
    $messages->addMessage(new Message("warning", $warning, true));
  }

  # Send everything to the templates.
  $tpl->set('nop', $nop, true);
  $tpl->set('catFull', $catFull, true);
  $tpl->set('pictures', $pictures, true);

  fetch();
?>
