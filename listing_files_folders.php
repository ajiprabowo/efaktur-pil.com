<?php
/* 

Free PHP File Directory Listing Script - Version 1.10

The MIT License (MIT)

Copyright (c) 2015 Hal Gatewood

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


*** OPTIONS ***/

// TITLE OF PAGE
$title = "List of Files";

// STYLING (light or dark)
$color	= 'light';

// ADD SPECIFIC FILES YOU WANT TO IGNORE HERE
$ignore_file_list = array( '.htaccess', 'Thumbs.db', '.DS_Store', 'index.php', '.ftpquota' );

// ADD SPECIFIC FILE EXTENSIONS YOU WANT TO IGNORE HERE, EXAMPLE: array('psd','jpg','jpeg')
$ignore_ext_list = array( );

// SORT BY
$sort_by = 'name_asc'; // options: name_asc, name_desc, date_asc, date_desc

// ICON URL
$icon_url = 'https://i.imgur.com/VSyvrFz.png';

// TOGGLE SUB FOLDERS, SET TO false IF YOU WANT OFF
$toggle_sub_folders = true;

// FORCE DOWNLOAD ATTRIBUTE
$force_download = false;

// IGNORE EMPTY FOLDERS
$ignore_empty_folders = true;

// FUNCTIONS TO MAKE THE MAGIC HAPPEN, BEST TO LEAVE THESE ALONE
function clean_title($title)
{
	return ucwords( str_replace( array("-", "_"), " ", $title) );
}

function ext($filename) 
{
	return substr( strrchr( $filename,'.' ),1 );
}

function display_size($bytes, $precision = 2) 
{
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= (1 << (10 * $pow)); 
	return round($bytes, $precision) . '<span class="fs-0-8 ">' . $units[$pow] . "</span>";
}

function count_dir_files( $dir)
{
	$fi = new FilesystemIterator(__DIR__ . "/" . $dir, FilesystemIterator::SKIP_DOTS);
	return iterator_count($fi);
}

function get_directory_size($path)
{
    $bytestotal = 0;
    $path = realpath($path);
    if($path!==false && $path!='' && file_exists($path))
    {
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object)
        {
            $bytestotal += $object->getSize();
        }
    }
    
    return display_size($bytestotal);
}


// SHOW THE MEDIA BLOCK
function display_block( $file )
{
	global $ignore_file_list, $ignore_ext_list, $force_download;
	
	$file_ext = ext($file);
	if( !$file_ext AND is_dir($file)) $file_ext = "dir";
	if(in_array($file, $ignore_file_list)) return;
	if(in_array($file_ext, $ignore_ext_list)) return;
	
	$download_att = ($force_download AND $file_ext != "dir" ) ? " download='" . basename($file) . "'" : "";
	
	$rtn = "<div class=\"block\">";
	$rtn .= "<a href=\"$file\" class=\"$file_ext\"{$download_att} target=\"_blank\" rel=\"noopener noreferrer\">";
	$rtn .= "	<div class=\"img $file_ext\"></div>";
	$rtn .= "	<div class=\"name\">";
	
    
	if ($file_ext === "dir") 
	{
		$rtn .= "		<div class=\"file fs-1-2 bold\">" . basename($file) . "</div>";
		$rtn .= "		<div class=\"data upper size fs-0-7\"><span class=\"bold\">" . count_dir_files($file) . "</span> files</div>";
		$rtn .= "		<div class=\"data upper size fs-0-7\"><span class=\"bold\">Size:</span> " . get_directory_size($file) . "</div>";
		
	}
	else
	{
		$rtn .= "		<div class=\" fs-1-2 \">" . basename($file) . "</div>";
		$rtn .= "		<div class=\"data upper size fs-0-7\"><span>Size:</span> " . display_size(filesize($file)) . "</div>";
		$rtn .= "		<div class=\"data upper modified fs-0-7\"><span>Last modified:</span> " .  date("D. F jS, Y - h:ia", filemtime($file)) . "</div>";	
	}

	$rtn .= "	</div>";
	$rtn .= "	</a>";
	$rtn .= "</div>";
	return $rtn;
}


// RECURSIVE FUNCTION TO BUILD THE BLOCKS
function build_blocks( $items, $folder )
{
	global $ignore_file_list, $ignore_ext_list, $sort_by, $toggle_sub_folders, $ignore_empty_folders;
	
	$objects = array();
	$objects['directories'] = array();
	$objects['files'] = array();
	
	foreach($items as $c => $item)
	{
		if( $item == ".." OR $item == ".") continue;
	
		// IGNORE FILE
		if(in_array($item, $ignore_file_list)) { continue; }
	
		if( $folder && $item )
		{
			$item = "$folder/$item";
		}

		$file_ext = ext($item);
		
		// IGNORE EXT
		if(in_array($file_ext, $ignore_ext_list)) { continue; }
		
		// DIRECTORIES
		if( is_dir($item) ) 
		{
			$objects['directories'][] = $item; 
			continue;
		}      
        
		// FILE DATE
        $file_time = date("U", filemtime($item));
		
        
		// FILES
		if( $item )
		{
			$objects['files'][$file_time . "-" . $item] = $item;
		}
	}
	
	foreach($objects['directories'] as $c => $file)
	{
		$sub_items = (array) scandir( $file );
		
		if( $ignore_empty_folders )
		{
			$has_sub_items = false;
			foreach( $sub_items as $sub_item )
			{
				$sub_fileExt = ext( $sub_item );
				if( $sub_item == ".." OR $sub_item == ".") continue;
				if(in_array($sub_item, $ignore_file_list)) continue;
				if(in_array($sub_fileExt, $ignore_ext_list)) continue;
			
				$has_sub_items = true;
				break;	
			}
			
			if( $has_sub_items ) echo display_block( $file );
		}
		else
		{
			echo display_block( $file );
		}
		
		if( $toggle_sub_folders )
		{
			if( $sub_items )
			{
				echo "<div class='sub' data-folder=\"$file\">";
				build_blocks( $sub_items, $file );
				echo "</div>";
			}
		}
	}
	
	// SORT BEFORE LOOP
	if( $sort_by == "date_asc" ) { ksort($objects['files']); }
	elseif( $sort_by == "date_desc" ) { krsort($objects['files']); }
	elseif( $sort_by == "name_asc" ) { natsort($objects['files']); }
	elseif( $sort_by == "name_desc" ) { arsort($objects['files']); }
	
	foreach($objects['files'] as $t => $file)
	{
		$fileExt = ext($file);
		if(in_array($file, $ignore_file_list)) { continue; }
		if(in_array($fileExt, $ignore_ext_list)) { continue; }
		echo display_block( $file );
	}
}

?>