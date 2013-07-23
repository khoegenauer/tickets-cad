<?php
/*=======================================================================
// File: 	JPGRAPH_MGRAPH.PHP
// Description: Class to handle multiple graphs in the same image
// Created: 	2006-01-15
// Ver:		$Id: jpgraph_mgraph.php 781 2006-10-08 08:07:47Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/

//=============================================================================
// CLASS MGraph
// Description: Create a container image that can hold several graph 
//=============================================================================
/**
 * MGraph
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class MGraph {

    protected $img=NULL;
    protected $iCnt=0,$iGraphs = array(); // image_handle, x, y, fx, fy, sizex, sizey
    protected $iFillColor='white', $iCurrentColor=0;
    protected $lm=0,$rm=0,$tm=0,$bm=0;
    protected $iDoFrame = FALSE, $iFrameColor = 'black', $iFrameWeight = 1;
    protected $iLineWeight = 1;
    protected $expired=false;
    protected $img_format='png',$image_quality=75;
    protected $iWidth=NULL,$iHeight=NULL;
    protected $background_image='',$background_image_center=true,
	$backround_image_format='',$background_image_mix=100,
	$background_image_y=NULL, $background_image_x=NULL;

    // Create a new instane of the combined graph
    /**
     * MGraph
     * Insert description here
     *
     * @param $aWidth
     * @param $aHeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function MGraph($aWidth=NULL,$aHeight=NULL) {
	$this->iWidth = $aWidth;
	$this->iHeight = $aHeight;
    }

    // Specify background fill color for the combined graph
    /**
     * SetFillColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFillColor($aColor) {
	$this->iFillColor = $aColor;
    }

    // Add a frame around the combined graph
    /**
     * SetFrame
     * Insert description here
     *
     * @param $aFlg
     * @param $aColor
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFrame($aFlg,$aColor='black',$aWeight=1) {
	$this->iDoFrame = $aFlg;
	$this->iFrameColor = $aColor;
	$this->iFrameWeight = $aWeight;
    }

    // Specify a background image blend    
    /**
     * SetBackgroundImageMix
     * Insert description here
     *
     * @param $aMix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundImageMix($aMix) {
	$this->background_image_mix = $aMix ;
    }

    // Specify a background image
    /**
     * SetBackgroundImage
     * Insert description here
     *
     * @param $aFileName
     * @param $aCenter_aX
     * @param $aY
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundImage($aFileName,$aCenter_aX=NULL,$aY=NULL) {
	// Second argument can be either a boolean value or 
	// a numeric
	$aCenter=TRUE;
	$aX=NULL;

	if( $GLOBALS['gd2'] && !USE_TRUECOLOR ) {
	    JpGraphError::RaiseL(12001);
//("You are using GD 2.x and are trying to use a background images on a non truecolor image. To use background images with GD 2.x you <b>must</b> enable truecolor by setting the USE_TRUECOLOR constant to TRUE. Due to a bug in GD 2.0.1 using any truetype fonts with truecolor images will result in very poor quality fonts.");
	}
	if( is_numeric($aCenter_aX) ) {
	    $aX=$aCenter_aX;
	}

	// Get extension to determine image type
	$e = explode('.',$aFileName);
	if( !$e ) {
	    JpGraphError::RaiseL(12002,$aFileName);
//('Incorrect file name for MGraph::SetBackgroundImage() : '.$aFileName.' Must have a valid image extension (jpg,gif,png) when using autodetection of image type');
	}
	
	$valid_formats = array('png', 'jpg', 'gif');
	$aImgFormat = strtolower($e[count($e)-1]);
	if ($aImgFormat == 'jpeg')  {
	    $aImgFormat = 'jpg';
	}
	elseif (!in_array($aImgFormat, $valid_formats) )  {
	    JpGraphError::RaiseL(12003,$aImgFormat,$aFileName);
//('Unknown file extension ($aImgFormat) in MGraph::SetBackgroundImage() for filename: '.$aFileName);
	}    

	$this->background_image = $aFileName;
	$this->background_image_center=$aCenter;
	$this->background_image_format=$aImgFormat;
	$this->background_image_x = $aX;
	$this->background_image_y = $aY;
    }


    // Private helper function for backgound image
    /**
     * _loadBkgImage
     * Insert description here
     *
     * @param $aFile
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _loadBkgImage($aFile='') {
	if( $aFile == '' )
	    $aFile = $this->background_image;

	// Remove case sensitivity and setup appropriate function to create image
	// Get file extension. This should be the LAST '.' separated part of the filename
	$e = explode('.',$aFile);
	$ext = strtolower($e[count($e)-1]);
	if ($ext == "jpeg")  {
	    $ext = "jpg";
	}
	
	if( trim($ext) == '' ) 
	    $ext = 'png';  // Assume PNG if no extension specified

	$supported = imagetypes();
	if( ( $ext == 'jpg' && !($supported & IMG_JPG) ) ||
	    ( $ext == 'gif' && !($supported & IMG_GIF) ) ||
	    ( $ext == 'png' && !($supported & IMG_PNG) ) ) {
	    JpGraphError::RaiseL(12004,$aFile);//('The image format of your background image ('.$aFile.') is not supported in your system configuration. ');
	}

	if( $ext == "jpg" || $ext == "jpeg") {
	    $f = "imagecreatefromjpeg";
	    $ext = "jpg";
	}
	else {
	    $f = "imagecreatefrom".$ext;
	}

	$img = @$f($aFile);
	if( !$img ) {
	    JpGraphError::RaiseL(12005,$aFile);
//(" Can't read background image: '".$aFile."'");   
	}
	return $img;
    }	

    /**
     * _strokeBackgroundImage
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _strokeBackgroundImage() {
	if( $this->background_image == '' ) 
	    return;

	$bkgimg = $this->_loadBkgImage();
	// Background width & Heoght
	$bw = imagesx($bkgimg);
	$bh = imagesy($bkgimg);
	// Canvas width and height
	$cw = imagesx($this->img);
	$ch = imagesy($this->img);

	if( $this->background_image_x === NULL || $this->background_image_y === NULL ) {
	    if( $this->background_image_center ) {
		// Center original image in the plot area
		$x = round($cw/2-$bw/2); $y = round($ch/2-$bh/2);
	    }
	    else {
		// Just copy the image from left corner, no resizing
		$x=0; $y=0;
	    }			
	}
	else {
	    $x = $this->background_image_x;
	    $y = $this->background_image_y;
	}
	$this->_imageCp($bkgimg,$x,$y,0,0,$bw,$bh,$this->background_image_mix);
    }

    /**
     * _imageCp
     * Insert description here
     *
     * @param $aSrcImg
     * @param $x
     * @param $y
     * @param $fx
     * @param $fy
     * @param $w
     * @param $h
     * @param $mix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _imageCp($aSrcImg,$x,$y,$fx,$fy,$w,$h,$mix=100) {
	imagecopymerge($this->img,$aSrcImg,$x,$y,$fx,$fy,$w,$h,$mix);
    }

    /**
     * _imageCreate
     * Insert description here
     *
     * @param $aWidth
     * @param $aHeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _imageCreate($aWidth,$aHeight) {
	if( $aWidth <= 1 || $aHeight <= 1 ) {
	    JpGraphError::RaiseL(12006,$aWidth,$aHeight);
//("Illegal sizes specified for width or height when creating an image, (width=$aWidth, height=$aHeight)");
	}
	if( @$GLOBALS['gd2']==true && USE_TRUECOLOR ) {
	    $this->img = @imagecreatetruecolor($aWidth, $aHeight);
	    if( $this->img < 1 ) {
		JpGraphError::RaiseL(12011);
// die("<b>JpGraph Error:</b> Can't create truecolor image. Check that you really have GD2 library installed.");
	    }
	    ImageAlphaBlending($this->img,true);
	} else {
	    $this->img = @imagecreate($aWidth, $aHeight);	
	    if( $this->img < 1 ) {
		JpGraphError::RaiseL(12012);
// die("<b>JpGraph Error:</b> Can't create image. Check that you really have the GD library installed.");
	    }
	}
    }

    /**
     * _polygon
     * Insert description here
     *
     * @param $p
     * @param $closed
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _polygon($p,$closed=FALSE) {
	if( $this->iLineWeight==0 ) return;
	$n=count($p);
	$oldx = $p[0];
	$oldy = $p[1];
	for( $i=2; $i < $n; $i+=2 ) {
	    imageline($this->img,$oldx,$oldy,$p[$i],$p[$i+1],$this->iCurrentColor);
	    $oldx = $p[$i];
	    $oldy = $p[$i+1];
	}
	if( $closed ) {
	    imageline($this->img,$p[$n*2-2],$p[$n*2-1],$p[0],$p[1],$this->iCurrentColor);
	}
    }

    /**
     * _filledPolygon
     * Insert description here
     *
     * @param $pts
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _filledPolygon($pts) {
	$n=count($pts);
	for($i=0; $i < $n; ++$i) 
	    $pts[$i] = round($pts[$i]);
	imagefilledpolygon($this->img,$pts,count($pts)/2,$this->iCurrentColor);
    }
	
    /**
     * _rectangle
     * Insert description here
     *
     * @param $xl
     * @param $yu
     * @param $xr
     * @param $yl
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _rectangle($xl,$yu,$xr,$yl) {
	for($i=0; $i < $this->iLineWeight; ++$i ) 
	    $this->_polygon(array($xl+$i,$yu+$i,$xr-$i,$yu+$i,
				  $xr-$i,$yl-$i,$xl+$i,$yl-$i,
				  $xl+$i,$yu+$i));
    }
	
    /**
     * _filledRectangle
     * Insert description here
     *
     * @param $xl
     * @param $yu
     * @param $xr
     * @param $yl
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _filledRectangle($xl,$yu,$xr,$yl) {
	$this->_filledPolygon(array($xl,$yu,$xr,$yu,$xr,$yl,$xl,$yl));
    }

    /**
     * _setColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _setColor($aColor) {
	$this->iCurrentColor = $this->iRGB->Allocate($aColor);
    }

    /**
     * AddMix
     * Insert description here
     *
     * @param $aGraph
     * @param $x
     * @param $y
     * @param $mix
     * @param $fx
     * @param $fy
     * @param $w
     * @param $h
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddMix($aGraph,$x=0,$y=0,$mix=100,$fx=0,$fy=0,$w=0,$h=0) {
	$this->_gdImgHandle($aGraph->Stroke( _IMG_HANDLER),$x,$y,$fx=0,$fy=0,$w,$h,$mix);
    }
    
    /**
     * Add
     * Insert description here
     *
     * @param $aGraph
     * @param $x
     * @param $y
     * @param $fx
     * @param $fy
     * @param $w
     * @param $h
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Add($aGraph,$x=0,$y=0,$fx=0,$fy=0,$w=0,$h=0) {
	$this->_gdImgHandle($aGraph->Stroke( _IMG_HANDLER),$x,$y,$fx=0,$fy=0,$w,$h);
    }

    /**
     * _gdImgHandle
     * Insert description here
     *
     * @param $agdCanvas
     * @param $x
     * @param $y
     * @param $fx
     * @param $fy
     * @param $w
     * @param $h
     * @param $mix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _gdImgHandle($agdCanvas,$x,$y,$fx=0,$fy=0,$w=0,$h=0,$mix=100) {
	if( $w == 0 )  $w = @imagesx($agdCanvas);
	if( $w === NULL ) {
	    JpGraphError::RaiseL(12007);
//('Argument to MGraph::Add() is not a valid GD image handle.');
	    return;
	}
	if( $h == 0 )  $h = @imagesy($agdCanvas);
	$this->iGraphs[$this->iCnt++] = array($agdCanvas,$x,$y,$fx,$fy,$w,$h,$mix);
    }

    /**
     * SetMargin
     * Insert description here
     *
     * @param $lm
     * @param $rm
     * @param $tm
     * @param $bm
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMargin($lm,$rm,$tm,$bm) {
	$this->lm = $lm;
	$this->rm = $rm;
	$this->tm = $tm;
	$this->bm = $bm;
    }

    /**
     * SetExpired
     * Insert description here
     *
     * @param $aFlg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetExpired($aFlg=true) {
	$this->expired = $aFlg;
    }

    // Generate image header
    /**
     * Headers
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Headers() {
	
	// In case we are running from the command line with the client version of
	// PHP we can't send any headers.
	$sapi = php_sapi_name();
	if( $sapi == 'cli' )
	    return;
	
	if( headers_sent() ) {
	    
	    echo "<table border=1><tr><td><font color=darkred size=4><b>JpGraph Error:</b> 
HTTP headers have already been sent.</font></td></tr><tr><td><b>Explanation:</b><br>HTTP headers have already been sent back to the browser indicating the data as text before the library got a chance to send it's image HTTP header to this browser. This makes it impossible for the library to send back image data to the browser (since that would be interpretated as text by the browser and show up as junk text).<p>Most likely you have some text in your script before the call to <i>Graph::Stroke()</i>. If this texts gets sent back to the browser the browser will assume that all data is plain text. Look for any text, even spaces and newlines, that might have been sent back to the browser. <p>For example it is a common mistake to leave a blank line before the opening \"<b>&lt;?php</b>\".</td></tr></table>";

	die();

	}	
	
	if ($this->expired) {
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
	    header("Cache-Control: no-cache, must-revalidate");
	    header("Pragma: no-cache");
	}
	header("Content-type: image/$this->img_format");
    }

    /**
     * SetImgFormat
     * Insert description here
     *
     * @param $aFormat
     * @param $aQuality
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetImgFormat($aFormat,$aQuality=75) {
	$this->image_quality = $aQuality;
	$aFormat = strtolower($aFormat);
	$tst = true;
	$supported = imagetypes();
	if( $aFormat=="auto" ) {
	    if( $supported & IMG_PNG )
		$this->img_format="png";
	    elseif( $supported & IMG_JPG )
		$this->img_format="jpeg";
	    elseif( $supported & IMG_GIF )
		$this->img_format="gif";
	    else
		JpGraphError::RaiseL(12008);
//(" Your PHP (and GD-lib) installation does not appear to support any known graphic formats.".
	    return true;
	}
	else {
	    if( $aFormat=="jpeg" || $aFormat=="png" || $aFormat=="gif" ) {
		if( $aFormat=="jpeg" && !($supported & IMG_JPG) )
		    $tst=false;
		elseif( $aFormat=="png" && !($supported & IMG_PNG) ) 
		    $tst=false;
		elseif( $aFormat=="gif" && !($supported & IMG_GIF) ) 	
		    $tst=false;
		else {
		    $this->img_format=$aFormat;
		    return true;
		}
	    }
	    else 
		$tst=false;
	    if( !$tst )
		JpGraphError::RaiseL(12009,$aFormat);
//(" Your PHP installation does not support the chosen graphic format: $aFormat");
	}
    }

    // Stream image to browser or to file
    /**
     * Stream
     * Insert description here
     *
     * @param $aFile
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stream($aFile="") {
	$func="image".$this->img_format;
	if( $this->img_format=="jpeg" && $this->image_quality != null ) {
	    $res = @$func($this->img,$aFile,$this->image_quality);
	}
	else {
	    if( $aFile != "" ) {
		$res = @$func($this->img,$aFile);
	    }
	    else
		$res = @$func($this->img);
	}
	if( !$res )
	    JpGraphError::RaiseL(12010,$aFile);
//("Can't create or stream image to file $aFile Check that PHP has enough permission to write a file to the current directory.");
    }

    /**
     * Stroke
     * Insert description here
     *
     * @param $aFileName
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aFileName='') {
	// Find out the necessary size for the container image
	$w=0; $h=0;
	for($i=0; $i < $this->iCnt; ++$i ) {
	    $maxw = $this->iGraphs[$i][1]+$this->iGraphs[$i][5];
	    $maxh = $this->iGraphs[$i][2]+$this->iGraphs[$i][6];
	    $w = max( $w, $maxw );
	    $h = max( $h, $maxh );
	}
	$w += $this->lm+$this->rm;
	$h += $this->tm+$this->bm;

	// User specified width,height overrides
	if( $this->iWidth !== NULL ) $w = $this->iWidth;
	if( $this->iHeight!== NULL ) $h = $this->iHeight;

	$this->_imageCreate($w,$h);
	$this->iRGB = new RGB($this->img);

	$this->_setcolor($this->iFillColor);
	$this->_filledRectangle(0,0,$w-1,$h-1);

	$this->_strokeBackgroundImage();

	if( $this->iDoFrame ) {
	    $this->_setColor($this->iFrameColor);
	    $this->iLineWeight=$this->iFrameWeight;
	    $this->_rectangle(0,0,$w-1,$h-1);
	}

	// Copy all sub graphs to the container
	for($i=0; $i < $this->iCnt; ++$i ) {
	    $this->_imageCp($this->iGraphs[$i][0],
			    $this->iGraphs[$i][1]+$this->lm,$this->iGraphs[$i][2]+$this->tm,
			    $this->iGraphs[$i][3],$this->iGraphs[$i][4],
			    $this->iGraphs[$i][5],$this->iGraphs[$i][6],
			    $this->iGraphs[$i][7]);
	}

	// Output image
	if( $aFileName == _IMG_HANDLER ) {
	    return $this->img;
	}
	else {
	    $this->Headers();
	    $this->Stream($aFileName);
	}
    }
}

?>
