<?php
/*
 * Copyright 2013 Jan Eichhorn <exeu65@googlemail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
/**
 * Set relevent store front
 */

switch ($_SESSION['location']['code']) {
	case 'CA':
		$_SESSION['location']['store'] = 'ca';
		break;
	case 'DE':
		$_SESSION['location']['store'] = 'de';
		break;
	case 'FR':
		$_SESSION['location']['store'] = 'fr';
		break;
	case 'JP':
		$_SESSION['location']['store'] = 'co.jp';
		break;
	case 'IT':
		$_SESSION['location']['store'] = 'it';
		break;
	case 'CN':
		$_SESSION['location']['store'] = 'cn';
		break;
	case 'ES':
		$_SESSION['location']['store'] = 'es';
		break;
	case 'IN':
		$_SESSION['location']['store'] = 'in';
		break;
	case 'GB':
		$_SESSION['location']['store'] = 'co.uk';
		break;
	default:
		$_SESSION['location']['store'] = 'com';
		break;
}

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}
$loader->add('ApaiIO\Test', __DIR__);
