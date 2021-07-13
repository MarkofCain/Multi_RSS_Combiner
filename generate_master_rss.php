<?php
#####   markcain@markcain.com
####    July 11, 2021 - 0.1.3
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
#   -- an xlm file will be generated into the specified folder
#   -- add a line to the crontab on the server to run this script on an hourly basis.  Such as:
#           00 * * * * /usr/local/bin/php /home/markcain/public_html/feed/generate_master_rss.php
#
##
##########

##########
###
##
#   Put the published title of each podcast and its repective URL into an associative array -- I like human readable variables
#   Modify this list as needed.  No comma needed on the last element of the associative array
#
$individual_podcasts = [
        "Destination Linux" => "https://destinationlinux.org/feed/mp3",
        "This Week in Linux" => "https://tuxdigital.com/feed/thisweekinlinux-mp3",
        "Hardware Addicts" => "https://hardwareaddicts.org/rss",
        "Sudo Show" => "https://sudo.show/rss",
        "Game Sphere" => "https://gamesphere.show/rss",
        "DLN Xtend" => "https://dlnxtend.com/rss"
    ];
#

# create a variable needed for later in the program
$all_items = array();  // instantiate the array $all_items


##########
###
##
#   Generate the content that is needed to populate the channel element.  RSS Standards only allow one channel element.
#   Utilize the heredoc method of assignment to allow easy spacing, quotes, new lines, etc
#
$new_channel_content = <<< CONTENT_END
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" encoding="UTF-8"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:atom="http://www.w3.org/2005/Atom/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:fireside="http://fireside.fm/modules/rss/fireside"
    xmlns:googleplay="http://www.google.com/schemas/play-podcasts/1.0"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:podcast="https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md"
    xmlns:rawvoice="http://www.rawvoice.com/rawvoiceRssModule/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
>
<channel>
	<title>Destination Linux Network Podcasts</title>
	<atom:link href="https://destinationlinux.org/feed/all_podcasts.xml" rel="self" type="application/rss+xml" />
	<link>https://destinationlinux.org</link>
	<description>Destination Linux Network is a collection of podcasts made by people who love running Linux and want to help others do the same.</description>
	<lastBuildDate>place_holder</lastBuildDate>
	<language>en-US</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<image>
        <url>https://destinationlinux.org/wp-content/uploads/2020/09/cropped-destination-linux-favicon-32x32.png</url>
        <title>Destination Linux</title>
        <link>https://destinationlinux.org</link>
        <width>32</width>
        <height>32</height>
    </image>

	<itunes:summary>Destination Linux Network is a collection of podcasts creators who share their passion for Linux &amp; Open Source. Website: https://destinationlinux.org | Network: https://destinationlinux.network</itunes:summary>
	<itunes:author>Destination Linux Network</itunes:author>
	<itunes:explicit>clean</itunes:explicit>
	<itunes:image href="https://destinationlinux.org/wp-content/uploads/2021/06/dln-podcast-art-destination-linux-scaled.jpg" />
	<itunes:type>episodic</itunes:type>
	<itunes:owner>
		<itunes:name>Destination Linux Network</itunes:name>
		<itunes:email>comments@destinationlinux.org</itunes:email>
	</itunes:owner>
	<managingEditor>comments@destinationlinux.org (Destination Linux Network)</managingEditor>
	<itunes:subtitle>A conversational podcast by people who love running Linux.</itunes:subtitle>
	<image>
		<title>Destination Linux</title>
		<url>https://destinationlinux.org/wp-content/uploads/2021/06/dln-podcast-art-destination-linux-scaled.jpg</url>
		<link>https://destinationlinux.org</link>
	</image>
	<itunes:category text="Technology" />
	<googleplay:category text="Technology"/>
	<itunes:category text="News">
		<itunes:category text="Tech News" />
	</itunes:category>
	<itunes:category text="Education" />
	<rawvoice:donate href="https://destinationlinux.org/patreon">Become a Patron</rawvoice:donate>
	<podcast:funding url="https://destinationlinux.org/patreon">Become a Patron</podcast:funding>
	<rawvoice:subscribe feed="https://destinationlinux.org/feed/all_podcasts" itunes="https://podcasts.apple.com/us/podcast/destination-linux/id1192543917" blubrry="https://blubrry.com/destinationlinux/" stitcher="https://www.stitcher.com/show/destination-linux" tunein="https://tunein.com/podcasts/Technology-Podcasts/Destination-Linux-p1156321/" spotify="https://open.spotify.com/show/0j1XLigWiYAUVtwzsQMz8l" amazon="https://music.amazon.com/podcasts/0dc3f02c-5b96-4b93-99fb-0f4fc15aabb3/Destination-Linux" pcindex="https://podcastindex.org/podcast/54545" iheart="https://www.iheart.com/podcast/269-destination-linux-62567534/" podchaser="https://www.podchaser.com/podcasts/destination-linux-507417"></rawvoice:subscribe>

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
#   Grab the current heading from the Destination Linux podcast and use it as the new heading for the master rss feeds.
#   The function "file_get_contents" reads the current rss feed from the URL of Destination Linux.
#   explode separates the file on the word <channel> into two sections
#   list assigns the content into a scalar varaible rather than an array
#
#    list($new_heading) = explode("<channel>", file_get_contents($individual_podcasts['Destination Linux']), 2);
#
##
###
##########

##########
###
##
#   Cycle through the individual podcast feeds one by one and get the content between the opening and closing item elements i.e. every <item> and </item>

foreach ($individual_podcasts as $podcast_name => $podcast_rss_url) {

    #  instantiate the array for the items
    $items = array();

    #  as each RSS feed is read, split the feed on all occurances of <item> and pack each <item> into an array $items
    $items = explode("<item>", file_get_contents($podcast_rss_url));

    #  The first element of the array $items unforunately contains all of the data that comes before the first <item>; Therefore, delete it via array_shift.
    $garbage = array_shift($items);  // The garbage is now removed from the array $items

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
# sort the master list of items by the key (which is the timestamp) in reverse order
krsort($master_items);

#file_put_contents("/home/markcain/public_html/feed/all_podcasts.xml", $new_heading);
file_put_contents("/home/markcain/public_html/feed/all_podcasts.xml", $new_channel_content, FILE_APPEND);

foreach ($master_items as $key => $individual_item) {
    file_put_contents("/home/markcain/public_html/feed/all_podcasts.xml", $individual_item, FILE_APPEND);
};

file_put_contents("/home/markcain/public_html/feed/all_podcasts.xml", "\n</channel>\n</rss>\n\n ", FILE_APPEND);

print "Done."
?>
