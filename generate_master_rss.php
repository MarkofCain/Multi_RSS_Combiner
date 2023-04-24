<?php
#####   markcain@markcain.com
####    April 24, 2023 - 0.1.43
###
##
#      __  __                  _           ____           _
#     |  \/  |   __ _   _ __  | | __      / ___|   __ _  (_)  _ __
#     | |\/| |  / _` | | '__| | |/ /     | |      / _` | | | | '_ \
#     | |  | | | (_| | | |    |   <      | |___  | (_| | | | | | | |
#     |_|  |_|  \__,_| |_|    |_|\_\      \____|  \__,_| |_| |_| |_|
#
##
###
####
#####   This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
#####   as published by the Free Software Foundation; either version 3 of the License, or any later version.
#####   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
#####   of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
####
###
##
#
#   PHP Script to combine all of the rss feeds associated with Destination Linux Network into a single RSS feed.  This is basic procedural PHP
#   -- Each RSS feed of the DLN Network is read.
#   -- Each <item> (podcast details) is stored in a master array.
#   -- Each <title> of each <item> has the title of the podcast added as some titles don't have the name of the podcast (the lack of uniformity is noted)
#   -- The master array is sorted by published date of each individual podcast
#   -- A boiler plate header copied from the Destination Linux Podcast is added to the new RSS feed
#   -- A custom <channel> header is created for the new RSS feed -- NOTE: the contents of rawvoice needs to be reviewed
#   -- Each <item> (podcast details) is added by date
#   -- The footer of the RSS feed is added
#   -- an xlm file will be generated into the specified "target_folder"
#   -- add a line to the crontab on the server to run this script on an hourly basis.  Such as:
#           00 * * * * /usr/local/bin/php /home/markcain/public_html/feed/generate_master_rss.php
#
##
##########

##########
###
##
#   Modify the following five variables to customize the use of this script
#
#	The list of podcasts to rake.  Modify this list as needed.  Use a human readable name followed by the rss feed of that podcast.
#	No comma needed on the last element of the associative array
# "Hardware Addicts" => "https://hardwareaddicts.org/rss",
# "Sudo Show" => "https://sudo.show/rss",
# "Game Sphere" => "https://gamesphere.show/rss",
# 
#
	$individual_podcasts = [
		"Destination Linux" => "https://destinationlinux.org/feed/mp3",
		"This Week in Linux" => "https://tuxdigital.com/feed/thisweekinlinux-mp3",
		"Hardware Addicts" => "https://feeds.fireside.fm/hardwareaddicts/rss",
		"Sudo Show" => "https://feeds.fireside.fm/sudoshow/rss",
		"Game Sphere" => "https://feeds.fireside.fm/gamesphere/rss",
		"The Fedora Project" => "https://feeds.fireside.fm/fedoraproject/rss",
		"Linux Out Loud" => "https://feeds.fireside.fm/dlnxtend/rss"
		];
#
#	The web root folder
#
		$web_root_path = "/home/markcain/public_html/";
#
#	The target folder of where you want surfers to find the new rss feed.
#
		$target_folder = "dln_feed/";  // use "" if you want it in the root of the domain; otherwise be sure to use a trailing slash /
#
#	The target file name of the new rss feed.  The extension is inconsequential and can even be omitted for a clean look like: rss
#
		$target_file= "all_podcasts.xml";

#
#	The target domain of the new rss feed.
#
		$target_domain= "https://MarkCain.com/";

# create a variable needed for later in the program
$all_items = array();  // instantiate the array $all_items


##########
###
##
#   Generate the content that is needed to populate the channel element.  RSS Standards only allow one channel element.
#   Utilize the heredoc method of assignment to allow easy spacing, quotes, new lines, etc
#
$new_channel_content = <<< CONTENT_END
<channel>
	<title>Destination Linux Network: All Shows</title>
	<atom:link href="$target_domain$target_folder$target_file" rel="self" type="application/rss+xml" />
	<link>$target_domain</link>
	<description>Destination Linux Network is a collection of podcasts made by people who love running Linux and want to help others do the same.</description>
	<lastBuildDate>place_holder</lastBuildDate>
	<language>en-US</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<itunes:summary>Destination Linux Network is a collection of podcasts creators who share their passion for Linux &amp; Open Source. Website: https://destinationlinux.org | Network: https://destinationlinux.network</itunes:summary>
	<itunes:author>Destination Linux Network: All Shows</itunes:author>
	<itunes:explicit>clean</itunes:explicit>
	<itunes:image href="https://destinationlinux.org/wp-content/uploads/2021/06/dln-podcast-art-destination-linux-scaled.jpg" />
	<itunes:type>episodic</itunes:type>
	<itunes:owner>
		<itunes:name>Destination Linux Network: All Shows</itunes:name>
		<itunes:email>comments@destinationlinux.network</itunes:email>
	</itunes:owner>
	<managingEditor>comments@destinationlinux.network (Destination Linux Network: All Shows)</managingEditor>
	<itunes:subtitle>A collection of conversational podcasts by people who love running Linux.</itunes:subtitle>
	<image>
		<title>Destination Linux Network: All Shows</title>
		<url>https://destinationlinux.org/wp-content/uploads/2021/06/dln-podcast-art-destination-linux-scaled.jpg</url>
		<link>$target_domain</link>
	</image>
	<itunes:category text="Technology" />
	<googleplay:category text="Technology"/>
	<itunes:category text="News">
		<itunes:category text="Tech News" />
	</itunes:category>
	<itunes:category text="Education" />
	<rawvoice:donate href="https://destinationlinux.org/patreon">Become a Patron</rawvoice:donate>
	<podcast:funding url="https://destinationlinux.org/patreon">Become a Patron</podcast:funding>

CONTENT_END;
#
#


##########
###
##
#   Generate the current time stamp according to the RSS pubdate standard rfc822.
#   swap it for the placeholder in the heredoc above
#
    $now_pubdate = date("D, j M Y G:i:s -0500");  #Mon, 05 Jul 2021 10:00:00 -0500 (5 hours behind UT which is Eastern Standard Time -- close enough)
    $new_channel_content = str_replace("<lastBuildDate>place_holder</lastBuildDate>", "<lastBuildDate>$now_pubdate</lastBuildDate>", "$new_channel_content");
#
##
###
##########

##########
###
##
#  Create a curl session function to replace the problematic php file_get_contents function
    
function curl_file_get_contents($passed_url) {
    
    # initialize a new curl handle
    $ch = curl_init(); 

    ## set some options:
    
        # setting RETURNTRANSFER to 1 tells curl we want a return string and not a display
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // tells curl we want a return string and not a display
    
        # tells curl the target url
        curl_setopt($ch, CURLOPT_URL, $passed_url); 
	
    # execute the curl session.  $contents will be the file if successful; otherwise false on failure
    $contents = curl_exec($ch);

    # close the curl session
    curl_close($ch);
    
    # return the contents
    return $contents;
};

#
##
###
##########
 
 
##########
###
##
#   Cycle through the individual podcast feeds one by one and get the content between the opening and closing item elements i.e. every <item> and </item>

foreach ($individual_podcasts as $podcast_name => $podcast_rss_url) {

    #  instantiate the array for the items.  Needs to be instantiated on each pass as it gets unset at the end of each loop
    $items = array();

    #  as each RSS feed is read, split the feed on all occurances of <item> and pack each <item> into an array $items
    $items = explode("<item>", curl_file_get_contents($podcast_rss_url));

    #  The first element of the array $items unforunately contains all of the data that comes before the first <item>; Therefore, delete it via array_shift.
    $garbage = array_shift($items);  // The garbage is now removed from the array $items

	# The $garbage variable directly above contains the xml namespaces used for each of the podcasts.  Pluck the xml namespaces out of the garbage
	# and pack them into the array $name_spaces so that all the xml names spaces are reused.  This will future proof the script.

		# look for the occurance of "xmlns:" followed by data and ending with a double quote in the variable $garbage.  The "U" modifier makes it minimalistic or non-greedy; then store them in the array $matches
			preg_match_all('/(xmlns:.*=".*")/U', $garbage, $matches);

			# $matches is actaully an array of arrays; so we need to loop through the $matches[1] to get the desired values
			foreach ($matches[1] as $k1 => $v1){
				# use the data of the name space as the key.  This will ensure that duplicate xmlns' are overwritten and hence will occur only one in the array
	        	$name_spaces[substr($v1, 0, 30)] = $v1;
		    };

    # Since the rss feed was "exploded" on the string "<item>" the literal string "<item>" needs to be added back into each element of the array
    # To ensure that the published title of each podcast is also included it the title, add the podcast name to the <tile> element
    $items = preg_replace("/<title>/", "<item>\n<title>$podcast_name: ", $items);

    # This will remove any extraneous characters after the last </item> in the rss feed such as </channel> and </rss>
    $items[count($items) - 1] = preg_replace("/<\/channel>.*<\/rss>.*/s", "", $items[count($items) - 1]);

    # now let's copy all of these elements of $items over to the master array $all_items
    $all_items = array_merge($all_items, $items);

    # dump all contents of the array $items so we can start fresh with the next rss feed
    unset($items);

};
#
##
###
##########

##########
###
##
#   Now each <item> needs to be added to an array that is keyed on the pubdate of each item.  This will give us the ability to sort by the published date
#   All of this is facilited easily when you use the *nix time stamp as a key for the array
#

foreach ($all_items as $key => $individual_item) {

    # pluck the human readable date and time from between the <pubDate> and </pubDate> elements; then convert it to UNIX timestamp
    $time_stamp_pubdate = strtotime(preg_replace("/.*<pubDate>(.*)<\/pubDate>.*/s", "$1", $individual_item));

    # use the UNIX timestamp as a key for each element of the array
    $master_items["$time_stamp_pubdate"] = $individual_item;

};
#
##
###
##########

##########
###
##
#   Put all of these modified elements together to create the new master rss feed
#
	# sort a couple of the associative arrays for order
		# sort the master list of items by the key (which is the timestamp) in reverse order
			krsort($master_items);

		# sort the list of xml names spaces to make it pretty
			ksort($name_spaces);

	# write out the top of the rss feed
		file_put_contents("$web_root_path$target_folder$target_file", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>");

	# write out the beginning of the rss specifications for the name spaces
		file_put_contents("$web_root_path$target_folder$target_file", "\n<rss version=\"2.0\" encoding=\"UTF-8\"\n", FILE_APPEND);

	# write out the individual name space specifications
	 	foreach ($name_spaces as $k1 => $individual_xmlns) {
			file_put_contents("$web_root_path$target_folder$target_file", "   " . $individual_xmlns . "\n", FILE_APPEND);
		};

	# write out the end of the rss specifications for the name spaces
		file_put_contents("$web_root_path$target_folder$target_file", ">\n", FILE_APPEND);

	# write out the new channel content
		file_put_contents("$web_root_path$target_folder$target_file", $new_channel_content, FILE_APPEND);

	# write out the <item>s for each of the episodes"$web_root_path$target_folder$target_file"
	 	foreach ($master_items as $key => $individual_item) {
			file_put_contents("$web_root_path$target_folder$target_file", $individual_item, FILE_APPEND);
		};

	# write out the closing tags of the rss feeds
		file_put_contents("$web_root_path$target_folder$target_file", "\n</channel>\n</rss>\n\n ", FILE_APPEND);
