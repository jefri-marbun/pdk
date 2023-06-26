<?php

namespace silatng\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

// use simaranta\Http\Models\ActivityStreamModel;

// use PDF;

class SilatNGController extends Controller
{
	public function __construct()
	{
		// $configModel = ConfigModel::get();
		// foreach ($configModel as $key => $conf) {
		//     $this->data[$conf['name']] = $conf['value'];
		// }
		// $this->data['app_default_kodepos'] = KodePosModel::find($this->data['app_kode_pos_id']);
		// $this->data['backend_menu'] = BackendMenuModel::where('status', 'Active')->get();
		// $this->_setup_global();
	}

	// public function _setup_global()
	// {
	//     /**
	//      * Set Global Settings here
	//      * or you can fetch data from model here
	//     */
	//     $this->middleware(function ($request, $next) {
	//         $maintenanceStatus = false;
	//         if ($this->data['app_maintenance'] == 1) {
	//             $maintenanceStatus = true;
	//             $whitelistIPData = explode(',', str_replace(' ', '', $this->data['app_whitelist_ip']));
	//             foreach ($whitelistIPData as $key => $whitelist_ip) {
	//                 if (Request::ip() == $whitelist_ip) {
	//                     $maintenanceStatus = false;
	//                     break;
	//                 } else {
	//                     $maintenanceStatus = true;
	//                 }
	//             }
	//         }
	//         if ($maintenanceStatus == true) {
	//             if (Auth::user()->user_role_id == 1) { // Aministrator allow to access while Maintenance Mode is ON
	//                 if (Auth::check()) {
	//                     // User Access
	//                     $this->data['user_access'] = UserAccessModel::where('user_id', Auth::user()->id)->get();
	//                     // Notification
	//                     $this->data['notification_total_unread'] = NotificationModel::where(['user_id' => Auth::user()->id, 'status' => 0])->orderBy('id', 'DESC')->get();
	//                     $this->data['notification_5_unread'] = NotificationModel::where(['user_id' => Auth::user()->id, 'status' => 0])->orderBy('id', 'DESC')->take(5)->get();
	//                 }
	//                 return $next($request);
	//             } else {
	//                 return response(view('errors.maintenance'));
	//             }
	//         } else {
	//             if (Auth::check()) {
	//                 // User Access
	//                 $this->data['user_access'] = UserAccessModel::where('user_id', Auth::user()->id)->get();
	//                 // Notification
	//                 $this->data['notification_total_unread'] = NotificationModel::where(['user_id' => Auth::user()->id, 'status' => 0])->orderBy('id', 'DESC')->get();
	//                 $this->data['notification_5_unread'] = NotificationModel::where(['user_id' => Auth::user()->id, 'status' => 0])->orderBy('id', 'DESC')->take(5)->get();
	//             }
	//             return $next($request);
	//         }
	//     });
	// }

	public function create_captcha($data = '', $img_path = '', $img_url = '', $font_path = '')
	{
		$defaults = array('word' => '', 'img_path' => '', 'img_url' => '', 'img_width' => '150', 'img_height' => '30', 'font_path' => '', 'expiration' => 7200);

		foreach ($defaults as $key => $val) {
			if (!is_array($data)) {
				if (!isset($$key) or $$key == '') {
					$$key = $val;
				}
			} else {
				$$key = (!isset($data[$key])) ? $val : $data[$key];
			}
		}

		if ($img_path == '' or $img_url == '') {
			return FALSE;
		}

		if (!@is_dir($img_path)) {
			return FALSE;
		}
		if (!is_writable($img_path)) {
			return FALSE;
		}

		if (!extension_loaded('gd')) {
			return FALSE;
		}

		// -----------------------------------
		// Remove old images
		// -----------------------------------

		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);

		$current_dir = @opendir($img_path);

		while ($filename = @readdir($current_dir)) {
			if ($filename != "." and $filename != ".." and $filename != "index.html" and $filename != ".gitignore") {
				$name = str_replace(".jpg", "", $filename);

				if (($name + $expiration) < $now) {
					@unlink($img_path . $filename);
				}
			}
		}
		@closedir($current_dir);

		// -----------------------------------
		// Do we have a "word" yet?
		// -----------------------------------

		if ($word == '') {
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$str = '';
			for ($i = 0; $i < 8; $i++) {
				$str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
			}

			$word = $str;
		}

		// -----------------------------------
		// Determine angle and position
		// -----------------------------------

		$length	= strlen($word);
		$angle	= ($length >= 6) ? rand(- ($length - 6), ($length - 6)) : 0;
		$x_axis	= rand(6, (360 / $length) - 16);
		$y_axis = ($angle >= 0) ? rand($img_height, $img_width) : rand(6, $img_height);

		// -----------------------------------
		// Create image
		// -----------------------------------

		// PHP.net recommends imagecreatetruecolor(), but it isn't always available
		if (function_exists('imagecreatetruecolor')) {
			$im = imagecreatetruecolor($img_width, $img_height);
		} else {
			$im = imagecreate($img_width, $img_height);
		}
		// -----------------------------------
		//  Assign colors
		// -----------------------------------

		$bg_color		= imagecolorallocate($im, 255, 255, 255);
		// $border_color	= imagecolorallocate ($im, 153, 102, 102); // Old Colour
		$border_color	= imagecolorallocate($im, 23, 101, 253);
		// $text_color		= imagecolorallocate ($im, 204, 153, 153); // Old Colour
		$text_color		= imagecolorallocate($im, 23, 101, 253);
		$grid_color		= imagecolorallocate($im, 255, 182, 182);
		$shadow_color	= imagecolorallocate($im, 255, 240, 240);

		// -----------------------------------
		//  Create the rectangle
		// -----------------------------------

		ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

		// -----------------------------------
		//  Create the spiral pattern
		// -----------------------------------

		// $theta		= 1;
		// $thetac		= 7;
		// $radius		= 16;
		// $circles	= 20;
		// $points		= 32;

		// for ($i = 0; $i < ($circles * $points) - 1; $i++)
		// {
		//     $theta = $theta + $thetac;
		// 	$rad = $radius * ($i / $points );
		// 	$x = ($rad * cos($theta)) + $x_axis;
		// 	$y = ($rad * sin($theta)) + $y_axis;
		// 	$theta = $theta + $thetac;
		// 	$rad1 = $radius * (($i + 1) / $points);
		// 	$x1 = ($rad1 * cos($theta)) + $x_axis;
		// 	$y1 = ($rad1 * sin($theta )) + $y_axis;
		// 	imageline($im, $x, $y, $x1, $y1, $grid_color);
		// 	$theta = $theta - $thetac;
		// }

		// -----------------------------------
		//  Write the text
		// -----------------------------------

		$use_font = ($font_path != '' and file_exists($font_path) and function_exists('imagettftext')) ? TRUE : FALSE;

		if ($use_font == FALSE) {
			$font_size = 5;
			$x = rand(0, $img_width / ($length / 3));
			$y = 0;
		} else {
			// $font_size	= 16; // Old Size
			$font_size	= 20;
			$x = rand(0, $img_width / ($length / 1.5));
			$y = $font_size + 2;
		}

		for ($i = 0; $i < strlen($word); $i++) {
			if ($use_font == FALSE) {
				$y = rand(0, $img_height / 2);
				imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
				$x += ($font_size * 2);
			} else {
				$y = rand($img_height / 2, $img_height - 3);
				imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
				$x += $font_size;
			}
		}


		// -----------------------------------
		//  Create the border
		// -----------------------------------

		imagerectangle($im, 0, 0, $img_width - 1, $img_height - 1, $border_color);

		// -----------------------------------
		//  Generate the image
		// -----------------------------------

		$img_name = $now . '.jpg';

		ImageJPEG($im, $img_path . $img_name);

		$img = "<img src=\"$img_url$img_name\" width=\"$img_width\" height=\"$img_height\" style=\"border:0;\" alt=\" \" />";

		ImageDestroy($im);

		return array('word' => $word, 'time' => $now, 'image' => $img);
	}
}
