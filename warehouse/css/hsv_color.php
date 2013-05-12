<?php

    /**
     * Convert a hex colour string into an rgb array.
     *
     * Handles colour string in the following formats:
     *
     *     o #44FF55
     *     o 4FF55
     *     o #4F5
     *     o 4F5
     *
     * @return array
     * @param  string $hex
     * @access public
     */
    function hex2rgb($hex)
    {
        $hex = @preg_replace('/^#/', '', $hex);
        if (strlen($hex) == 3) {
            $v = explode(':', chunk_split($hex, 1, ':'));
            return array(16 * hexdec($v[0]) + hexdec($v[0]), 16 * hexdec($v[1]) + hexdec($v[1]), 16 * hexdec($v[2]) + hexdec($v[2]));
        } else {
            $v = explode(':', chunk_split($hex, 2, ':'));
            return array(hexdec($v[0]), hexdec($v[1]), hexdec($v[2]));
        }
    } 

    /**
     * Convert an rgb array into a hex colour string.
     *
     * Handles colour string in the following formats:
     *
     *     o #44FF55
     *     o 4FF55
     *     o #4F5
     *     o 4F5
     *
     * @return array
     * @param  string $hex
     * @access public
     */
    function rgb2hex($rgb, $adHash = true)
    {
        return sprintf("%s%02X%02X%02X", ($adHash ? '#' : ''), $rgb[0], $rgb[1], $rgb[2]);
    } 


    /**
     * Convert an RGB array into HLS colour space.
     *
     * Expects array(r, g, b) where r, g, b in [0,255].  The HLS array is
     * returned as array(h, l, s) where h is in [0,360], l and s in [0,1].
     *
     * Function adapted from 'Computer Graphics: Principles and Practice',
     * by Foley, van Dam, Feiner and Hughes.  Chapter 13; Achromatic and
     * Colored Light.
     *
     * @return array
     * @param  array $rgb
     * @access public
     */
    function rgb2hls($rgb)
    {
        for ($c=0; $c<3; $c++) {
            $rgb[$c] = $rgb[$c] / 255;
        }
        
        $hls = array(0, 0, 0);
        $max = max($rgb);
        $min = min($rgb);

        $hls[1] = ($max + $min) / 2;
        if ($max == $min) {
            $hls[0] = null;
            $hls[2] = 0;
        } else {
            $delta = $max - $min;
            $hls[2] = ($hls[1] <= 0.5) ? ($delta / ($max + $min)) : ($delta / (2 - $delta));

            if ($rgb[0] == $max) {
                $hls[0] = ($rgb[1] - $rgb[2]) / $delta;
            } else if ($rgb[1] == $max) {
                $hls[0] = 2 + ($rgb[2] - $rgb[0]) / $delta;
            } else {
                $hls[0] = 4 + ($rgb[0] - $rgb[1]) / $delta;
            }

            $hls[0] *= 60;
            if ($hls[0] < 0) {
                $hls[0] += 360;
            }
            if ($hls[0] > 360) {
                $hls[0] -= 360;
            }
        }
        ksort($hls);
        return $hls;
    } 



    /**
     * Convert HLS colour space array to RGB colour space.
     *
     * Expects HLS array  as array(h, l, s) where h in [0,360], l and s each
     * in [0,1].  Returns array(r, g, b) where r, g, and b each in [0, 255]
     *
     * Function adapted from 'Computer Graphics: Principles and Practice',
     * by Foley, van Dam, Feiner and Hughes.  Chapter 13; Achromatic and
     * Colored Light.
     *
     * @return array
     * @param  array $hls
     * @access public
     */
    function hls2rgb($hls)
    {
        $rgb = array(0, 0, 0);
        
        $m2 = ($hls[1] <= 0.5) ? ($hls[1] * (1 + $hls[2])) : ($hls[1] + $hls[2] * (1 - $hls[1]));
        $m1 = 2 * $hls[1] - $m2;

        if (!$hls[2]) {
            if ($hls[0] === null) {
                $rgb[0] = $rgb[1] = $rgb[2] = $hls[1];
            } else {
                return false;
            }
        } else {
            $rgb[0] = _hVal($m1, $m2, $hls[0] + 120);
            $rgb[1] = _hVal($m1, $m2, $hls[0]);
            $rgb[2] = _hVal($m1, $m2, $hls[0] - 120);
        }
        
        for ($c=0; $c<3; $c++) {
            $rgb[$c] = round($rgb[$c] * 255);
        }
        return $rgb;
    } 
	
	    /**
     * Hue value checker for HSL colour space routine.
     *
     * @return float
     * @param  float $n1
     * @param  float $n2
     * @param  float $h
     * @access private
     * @see    Image::hls2rgb()
     */
    function _hVal($n1, $n2, $h)
    {
        if ($h > 360) {
            $h -= 360;
        } else if ($h < 0) {
            $h += 360;
        }

        if ($h < 60) {
            return $n1 + ($n2 - $n1) * $h / 60;
        } else if ($h < 180) {
            return $n2;
        } else if ($h < 240) {
            return $n1 + ($n2 - $n1) * (240 - $h) / 60;
        } else {
            return $n1;
        }
    } 


	/*
	Funktion zum erstellen einer Farbnuance einer HEX-Farbe
	@return 	string
	@param 		string $color
	@access 	private
	*/

	function ct_hsvLuminance($color, $luminance)
	{
		// Hex-Farbe in RGB und RGB in HSV umwandeln
		$hsvcolor = rgb2hls(hex2rgb($color));

		// Satuartion der Farbe erhöhen
		$hsvcolor[1] = $hsvcolor[1]+$luminance;

		// HSV zurück nach RGB wandeln und RGB zurück nach GEX
		return rgb2hex(hls2rgb($hsvcolor));
	}	
	
	function ct_hsvSaturation($color,$saturation)
	{
		// Hex-Farbe in RGB und RGB in HSV umwandeln
		$hsvcolor = rgb2hls(hex2rgb($color));

		// Luminance der Farbe erhöhen
		$hsvcolor[2] = $hsvcolor[2]+$saturation;

		// HSV zurück nach RGB wandeln und RGB zurück nach GEX
		return rgb2hex(hls2rgb($hsvcolor));
	}
	
		function ct_hsvShade($color, $luminance, $saturation)
	{
		// Hex-Farbe in RGB und RGB in HSV umwandeln
		$hsvcolor = rgb2hls(hex2rgb($color));

		// Saturation und Luminance der Farbe erhöhen
		$hsvcolor[1] = $hsvcolor[1]+$luminance;
		$hsvcolor[2] = $hsvcolor[2]+$saturation;

		return rgb2hex(hls2rgb($hsvcolor));
	}	
	
?>