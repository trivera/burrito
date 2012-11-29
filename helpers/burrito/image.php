<?php 

defined('C5_EXECUTE') or die('Access Denied.');

class BurritoImageHelper extends ImageHelper {
	
	public function outputThumbnail($obj, $width=100, $height=100, $params=array()){
		
		$buh = Loader::helper('burrito/utility', 'burrito');
		
		$buh->defaults(array(
			'obj'		=> $obj,
			'width'		=> $width,
			'height'	=> $height,
			'alt'	 	=> null,
			'return' 	=> true,
			'crop'		=> false
		), $params);

		return call_user_func_array(array('parent', 'outputThumbnail'), $params);
	}
	
	/* 
		Rotate image and save rotated copies in the cache
		$image: gd image resource
	*/
	public function rotate($image, $filename, $degrees = 0, $quality = 80) {
		$absPath = DIR_FILES_CACHE . '/' . $filename;
		
		if (!file_exists($absPath)) {
			$rotated = imagerotate($image, $degrees, 0);
			imagejpeg($rotated, $absPath, $quality);
		}
		
		return $absPath;
	}
	
	/* 
		Joins an image with a mirrored version of itself for smoother tiling
	*/
	public function createTile($path, $height = 100) {
		$fh = Loader::helper('file');
		$cacheName = md5('tile_'.$path).'.'.$fh->getExtension($path);
		$cachePath = DIR_FILES_CACHE . '/' . $cacheName;
		
		if (!file_exists($cachePath)) {
			$original = imagecreatefromjpeg($path);
			$originalX = imagesx($original);
			$originalY = imagesy($original);
			
			$flipped = $this->flip($original, 'vertical');
			$flippedX = imagesx($flipped);
			$flippedY = imagesy($flipped);
			
			$tile = @imagecreatetruecolor($originalX * 2, $originalY);
			
			imagecopy($tile, $original, 0, 0, 0, 0, $originalX, $originalY);
			imagecopy($tile, $flipped, $flippedX, 0, 0, 0, $flippedX, $flippedY);
			
			imagejpeg($tile, $cachePath, 80); // save
			
			return $tile;
		}
		else {
			return imagecreatefromjpeg($cachePath);
		}
	}
	
	/* from http://us1.php.net/imagecopy comments section (modified to pass mode strings) */
	public function flip($imgsrc, $mode) {
		
		$modes = array(
			'horizontal' => 1,
			'vertical' => 2,
			'both' => 3
		);
		
		define ( 'IMAGE_FLIP_HORIZONTAL', 1 );
		define ( 'IMAGE_FLIP_VERTICAL', 2 );
		define ( 'IMAGE_FLIP_BOTH', 3 );
		
		$width                        =    imagesx ( $imgsrc );
		$height                       =    imagesy ( $imgsrc );
		
		$src_x                        =    0;
		$src_y                        =    0;
		$src_width                    =    $width;
		$src_height                   =    $height;
		
		switch ( (int) $modes[$mode] )
		{
		
			case IMAGE_FLIP_HORIZONTAL:
				$src_y                =    $height;
				$src_height           =    -$height;
			break;
		
			case IMAGE_FLIP_VERTICAL:
				$src_x                =    $width - 1;
				$src_width            =    -$width;
			break;
		
			case IMAGE_FLIP_BOTH:
				$src_x                =    $width;
				$src_y                =    $height;
				$src_width            =    -$width;
				$src_height           =    -$height;
			break;
		
			default:
				return $imgsrc;
		
		}
		
		$imgdest = imagecreatetruecolor ($width, $height);
		
		if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height ) )
		{
			return $imgdest;
		}
		
		return $imgsrc;
	}
	
	/* Doubles the image width and repeats itself */
	public function repeatImage($image) {
		$width = imagesx($image);
		$height = imagesy($image);
		
		$newImage = @imagecreatetruecolor($width * 2, $height);
		
		imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
		imagecopy($newImage, $image, $width, 0, 0, 0, $width, $height);
		
		return $newImage;
	}
	
	/*
		This takes an image and creates a 45 degree angle join with a 90 degree rotation of itself.
		Example: generating the corner of a tiled picture frame image.
		Can be used to create a "picture frame effect"
	*/
	public function createRightAngleJoin($image, $cornerRotate = 'tr') {
		
		// the source image needs to be at least as wide as it is tall
		while (imagesx($image) < imagesy($image)) {
			$image = $this->repeatImage($image);
		}
		
		$width = imagesx($image);
		$height = imagesy($image);
		
		// create a square image based on the height of the image
		$corner = @imagecreatetruecolor($height, $height);
		
		// put our original image into this new square image
		imagecopy($corner, $image, 0, 0, 0, 0, $width, $height);
		
		// create a transparent color 
		$transparent = imagecolorallocatealpha($corner, 255, 0, 0, 0);
		imagecolortransparent($corner, $transparent);
		
		// we want to cut out a triangle from the source image
		// these are the coordinates for that.
		$topJoinPoints = array(
			1, $height, // bottom left corner
			$height, $height, // bottom right corner
			$height, 1 // top right corner
		);
		
		// "cut out" the triangle from the source image
		imagefilledpolygon($corner, $topJoinPoints, 3, $transparent);
		
		// rotate the original image -90 degrees
		$rotated = imagerotate($image, -90, 0);
		
		$bottomJoinPoints = array(
			$height, 0, // top right corner
			0, 0, // top left corner
			0, $height // bottom left corner
		);
		
		// apply the transparency to that rotated image as well
		imagecolortransparent($rotated, $transparent);
		
		// cut out the opposite triangle from the rotated image
		imagefilledpolygon($rotated, $bottomJoinPoints, 3, $transparent);
		
		// create the top right corner image by merging the two triangles
		imagecopymerge($corner, $rotated, 0, 0, 0, 0, $height, $height, 100);
		
		if ($cornerRotate != 'tr') {
			// rotate the image if we need to return a corner different than "top right"
			$cornerAngles = array(
				'tr' => 0,
				'br' => 270,
				'bl' => 180,
				'tl' => 90
			);
			$corner = imagerotate($corner, $cornerAngles[$cornerRotate], 0);
		}
		
		return $corner;
	}
	
	public function serveJpeg($image) {
		header('Content-Type: image/jpeg');
		header('Last-Modified: '.gmdate('r', time()));
		header('Expires: '.gmdate('r', time() + 1800));
		
		imagejpeg($image);
		imagedestroy($image);
		
		exit;
	}
	
}