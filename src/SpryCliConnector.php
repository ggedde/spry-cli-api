<?php

/**
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

namespace Spry\SpryConnector;

use Spry\Spry;
use Spry\SpryUtilities;

/**
 * Connector for the Spy CLI
 */
class SpryCliConnector
{

    private static $cliPath = '';



    /**
     * Check if last DB Call had an Error
     *
     * @param string $cliPath
     *
     * @access public
     *
     * @return boolean
     */
    public static function run($cliPath = '')
    {
        self::$cliPath = $cliPath;

        $args = [];
        $configFile = '';
        $commands = [
            'c' => 'component',
            'clear' => 'clear',
            'h' => 'hash',
            'help' => 'help',
            'i' => 'init',
            'm' => 'migrate',
            't' => 'test',
            'u' => 'up',
            'v' => 'version',
            'l' => 'logs',
            'log' => 'logs',
            'p' => 'print',
        ];
        $command = '';
        $singletest = '';
        $hash = '';
        $component = '';
        $clear = '';
        $debug = false;
        $verbose = false;
        $skip = false;
        $repeat = 1;
        $port = 8000;
        $logs = '';
        $lines = '100';
        $print = '';
        $trace = false;
        $codeGap = 20;
        $keep = false;

        if (!empty($_SERVER['argv'])) {
            $args = $_SERVER['argv'];
            $key = array_search('--config', $args);
            if (false !== $key && isset($args[($key + 1)])) {
                $configFile = $args[($key + 1)];
            }

            $key = array_search('--verbose', $args);
            if (false !== $key) {
                $verbose = true;
            }

            $key = array_search('--skip', $args);
            if (false !== $key) {
                $skip = true;
            }

            $key = array_search('--keep', $args);
            if (false !== $key) {
                $keep = true;
            }

            $key = array_search('--debug', $args);
            if (false !== $key) {
                $debug = true;
            }

            $key = array_search('h', $args);
            if (false === $key) {
                $key = array_search('hash', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $hash = $args[($key + 1)];
            }

            $key = array_search('p', $args);
            if (false === $key) {
                $key = array_search('print', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $print = $args[($key + 1)];
            }

            $key = array_search('u', $args);
            if (false === $key) {
                $key = array_search('up', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $port = $args[($key + 1)];
            }

            $key = array_search('t', $args);
            if (false === $key) {
                $key = array_search('test', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $singletest = $args[($key + 1)];
            }

            $key = array_search('c', $args);
            if (false === $key) {
                $key = array_search('component', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $component = $args[($key + 1)];
            }

            $key = array_search('clear', $args);
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $clear = $args[($key + 1)];
            }

            $key = array_search('--repeat', $args);
            if (false !== $key && isset($args[($key + 1)])) {
                if (is_numeric($args[($key + 1)])) {
                    $repeat = floor($args[($key + 1)]);
                }
            }

            $key = array_search('l', $args);
            if (false === $key) {
                $key = array_search('log', $args);
            }
            if (false === $key) {
                $key = array_search('logs', $args);
            }
            if (false !== $key && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false) {
                $logs = $args[($key + 1)];
            }

            $key = array_search('--lines', $args);
            if (false !== $key && isset($args[($key + 1)])) {
                if (is_numeric($args[($key + 1)])) {
                    $lines = floor($args[($key + 1)]);
                }
            }

            $key = array_search('--trace', $args);
            if (false !== $key) {
                $trace = true;
            }

            foreach ($args as $value) {
                if (empty($command)) {
                    if (in_array($value, $commands)) {
                        $command = $value;
                    } elseif (in_array($value, array_keys($commands))) {
                        $command = $commands[$value];
                    }
                }
            }
        }

        if (!$command) {
            if (array_search('-v', $args) !== false || array_search('--version', $args) !== false) {
                $command = 'version';
            }

            if (array_search('-h', $args) !== false || array_search('--help', $args) !== false) {
                $command = 'help';
            }
        }

        if (!$command) {
            die("Spry -v ".Spry::getVersion()."\n\e[91mERROR:\e[0m Spry - Command not Found. For help try 'spry --help'");
        }

        if (strval($command) === 'version') {
            die("Spry -v ".Spry::getVersion());
        }

        if (strval($command) === 'help') {
            echo "Spry -v ".Spry::getVersion()."\n".
                "Usage: spry [command] [value] [--argument] [--argument]... \n\n".
                "List of Commands and arguments:\n\n".
                "\e[1mclear [object]                \e[0m- Clears specific objects.\n".
                "  [object]                    - logs | tests.\n".
                "  ex.     spry clear logs    (clears both API and PHP log files. Does not remove archived logs.)\n".
                "  ex.     spry clear tests   (deletes all test data in the database.)\n\n".
                "\e[1mcomponent | c [component] [--options]     \e[0m- Generate a new Component and add it to your component directory.\n".
                "  [component]                 - Name of new Component. Classes will follow psr-4 format\n".
                "  --with-routes               - Add default Routes to the config file.\n".
                "  --with-codes                - Add default Response Codes to the config file.\n".
                "  --with-tests                - Add default Tests to the config file.\n".
                "  --with-all                  - Add all defaults to the config file.\n".
                "  ex.     spry component sales_reps       (component classes will follow psr-4 format. ie SalesReps)\n".
                "  ex.     spry c sales_reps --with-all    (Adds SalesReps and adds Routes, Response Codes, and Tests to the config file)\n\n".
                "\e[1mhash | h [value]              \e[0m- Hash a value that procedes it using the salt in the config file.\n".
                "  ex.     spry hash something_to_hash_123\n".
                "  ex.     spry h \"hash with spaces 123\"\n\n".
                "\e[1mhelp | -h | --help            \e[0m- Display Information about Spry-cli.\n\n".
                "\e[1minit | i [public_directory]   \e[0m- Initiate a Spry Setup and Configuration with default project.\n".
                "  [public_directory]          - Creates a public endpoint directory with index.php.\n".
                "  ex.     spry init\n".
                "  ex.     spry i public        (creates a folder called 'public' and an index.php pointer file)\n\n".
                "\e[1mlogs | l [type] [--options]   \e[0m- Displays contents of log files.\n".
                "  [type]                      - php | api.\n".
                "  --lines                     - Number of lines to display. Default 100.\n".
                "  --trace                     - Only applies to 'type=php'. Adds trace to display.\n".
                "  ex.     spry logs api\n".
                "  ex.     spry l php --lines 10 --trace\n\n".
                "\e[1mmigrate | m [--options]       \e[0m- Migrate the Database Schema.\n".
                "  --dryrun  |  -d             - Only check for what will be migrated and report back. No actions will be taken.\n".
                "  --force   |  -f             - Delete Fields, Tables and other data that does not match the new Scheme.\n".
                "  --debug                     - Debug SQL, Shows SQL statements without running any actions.\n\n".
                "\e[1mnew | n [project]             \e[0m- Creates a new project and initiates it.\n".
                "  [project]                   - Name of project/directory to create and initialize.\n\n".
                "\e[1mprint | p [property]          \e[0m- Print the Property from the config in json.\n".
                "  [property]                  - Name of Property from the config to show data from.\n".
                "  ex.     spry print routes\n".
                "  ex.     spry print codes\n".
                "  ex.     spry p hooks\n".
                "  ex.     spry p filters\n\n".
                "\e[1mtest | t [test] [--options]   \e[0m- Run a Test or all Tests if a Test name is not specified. Then remove all test data in the database.\n".
                "  [test]                      - Name of test to run.  Leave blank for all tests.\n".
                "  --verbose                   - List out full details of the Test(s).\n".
                "  --repeat                    - Repeat the test(s) a number of times.\n".
                "  --skip                      - Run all tests even on Failed tests. Skips Fails.\n".
                "  --keep                      - Keeps the data in the Database.\n".
                "  ex.     spry test\n".
                "  ex.     spry test --verbose\n".
                "  ex.     spry t connection --verbose --repeat 4\n".
                "  ex.     spry t '{\"route\":\"/example/add\", \"params\":{\"name\":\"test\"}, \"expect\":{\"code\": 2000}}'\n\n".
                "\e[1mversion | v | -v | --version  \e[0m- Display the Version of the Spry Instalation.\n\n".
                "\e[1mup | u [port] [directory]     \e[0m- Start the Built in PHP Spry Server.\n".
                "  [port]                      - default is 8000.\n".
                "  [directory]                 - default is current directory.  Requires 'vendor/autoload.php'\n";
        }

        if (!$configFile) {
            $configFile = self::findConfig();
        }

        if (!$configFile || !file_exists($configFile)) {
            die("\e[91mERROR:\e[0m No Config File Found. Run SpryCli from the same folder that contains your 'config.php' file or specify the config file with --config");
        }

        // Load the Main Config Data and Set Autoloader and Configure Filters
        Spry::configure($configFile);

        switch ($command) {
            case 'component':
                $componentSanitized = preg_replace("/\W/", '', str_replace([' ', '-'], '_', $component));
                $componentName = str_replace(' ', '', ucwords(str_replace('_', ' ', $componentSanitized)));

                if (!$componentName) {
                    die("\e[91mERROR:\e[0m Missing Component Name.");
                }

                $sourceComponent = self::$cliPath.'/example_project/components/example.php';
                $newComponent = Spry::config()->componentsDir.'/'.$componentName.'.php';

                if (!is_dir(Spry::config()->componentsDir.'/')) {
                    die("\e[91mERROR:\e[0m Component Directory is not configured in config.php or not found.");
                }

                if (!is_writable(Spry::config()->componentsDir.'/')) {
                    die("\e[91mERROR:\e[0m Component Directory Does not seem to be writable.");
                }

                if (file_exists($newComponent)) {
                    die("\e[91mERROR:\e[0m Component with that name already exists.");
                }

                if (!file_exists($sourceComponent)) {
                    die("\e[91mERROR:\e[0m Missing Source Component Template.");
                }

                if (!copy($sourceComponent, $newComponent)) {
                    die("\e[91mERROR:\e[0m Component could not be created.");
                }

                // Replace Component config_content
                $componentContents = file_get_contents($newComponent);
                $componentContents = str_replace('class Examples', 'class '.$componentName, $componentContents);
                $componentContents = str_replace('Examples::', $componentName.'::', $componentContents);
                $componentContents = str_replace('Examples', SpryUtilities::plural($componentName), $componentContents);
                $componentContents = str_replace('Example', SpryUtilities::single($componentName), $componentContents);
                $componentContents = str_replace('examples', SpryUtilities::plural(strtolower($componentSanitized)), $componentContents);
                $componentContents = str_replace('example', SpryUtilities::single(strtolower($componentSanitized)), $componentContents);

                if (!empty(Spry::config()->responseCodes)) {
                    if ($groups = array_keys(Spry::config()->responseCodes)) {
                        rsort($groups);
                        $lastGroup = $groups[0];
                        $newGroupId = intval($lastGroup) + 1;

                        if (!empty($newGroupId) && !isset(Spry::config()->responseCodes[$newGroupId])) {
                            $componentContents = str_replace('private static $id = 1;', 'private static $id = '.$newGroupId.';', $componentContents);
                        }
                    }
                }

                file_put_contents($newComponent, $componentContents);

                echo "\n\e[92mComponent Created Successfully!\e[0m\n".$newComponent."\n";

                break;

            case 'clear':
                if (!$clear) {
                    die("\e[91mERROR:\e[0m Clear Object Missing.\n");
                }

                switch ($clear) {
                    case 'logs':
                        if (!empty(Spry::config()->logger['api_file']) && file_exists(Spry::config()->logger['api_file'])) {
                            if (file_put_contents(Spry::config()->logger['api_file'], '') !== false) {
                                echo "\e[92mCleared API Logs!\e[0m\n";
                            } else {
                                "\e[91mUnknown ERROR:\e[0m Clearing API Log.\n";
                            }
                        } else {
                            "\e[91mERROR:\e[0m Could not find API Log.\n";
                        }

                        if (!empty(Spry::config()->logger['php_file']) && file_exists(Spry::config()->logger['php_file'])) {
                            if (file_put_contents(Spry::config()->logger['php_file'], '') !== false) {
                                echo "\e[92mCleared PHP Logs!\e[0m\n";
                            } else {
                                "\e[91mUnknown ERROR:\e[0m Clearing PHP Log.\n";
                            }
                        } else {
                            "\e[91mERROR:\e[0m Could not find PHP Log.\n";
                        }

                        break;

                    case 'tests':
                        if (Spry::db()->deleteTestData()) {
                            echo "\e[92mCleared Test Data!\e[0m\n";
                        } else {
                            "\e[91mERROR:\e[0m Unknown Error Clearing Test Data.\n";
                        }

                        break;

                    default:
                        die("\e[91mERROR:\e[0m Unknown Clear Command.\n");

                        break;
                }

                break;

            case 'init':
                echo "\n\e[96mSpry init complete!\e[0m\n";
                echo "Folder 'spry' created.\n";

                if (is_writable($configFile) && is_readable($configFile)) {
                    $salt = sha1(rand(10000, 99999).uniqid(mt_rand(), true).rand(10000, 99999));
                    //echo $salt;
                    $configContents = str_replace("config->salt = '';", "config->salt = '".$salt."';", file_get_contents($configFile));
                    if ($configContents) {
                        if (file_put_contents($configFile, $configContents)) {
                            echo "Salt value auto generated.\n";
                        } else {
                            echo "\e[91mERROR:\e[0m Could not update config file salt value.\n";
                        }

                        echo "Update the rest of your config file accordingly: ".$configFile."\n";
                    }
                }

                exit;

                break;

            case 'hash':
                if (!$hash) {
                    die("\e[91mERROR:\e[0m Missing Hash Value.  If hashing a value that has spaces then wrap with \"\"");
                }

                die(SpryUtilities::hash($hash));

                break;

            case 'logs':
                if (empty($logs) || !in_array($logs, ['php', 'api'])) {
                    die("\e[91mERROR:\e[0m Missing Logs Value.  Either 'php' or 'api' is acceptable.");
                }

                $files = [
                    'php' => Spry::config()->logger['php_file'],
                    'api' => Spry::config()->logger['api_file'],
                ];

                if (!file_exists($files[$logs])) {
                    die("\e[91mERROR:\e[0m Cannot find Logs File (".(!empty($files[$logs]) ? $files[$logs] : '')."). Check your Configuration for correct settings.");
                }

                $f = fopen($files[$logs], "rb");
                if (false === $f) {
                    die("\e[91mERROR:\e[0m Could not read file (".(!empty($files[$logs]) ? $files[$logs] : '')."). Check the Permissions.");
                }

                $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
                fseek($f, -1, SEEK_END);

                if (fread($f, 1) !== "\n") {
                    $lines -= 1;
                }

                $output = '';
                $chunk = '';

                while (ftell($f) > 0 && $lines >= 0) {
                    $seek = min(ftell($f), $buffer);
                    fseek($f, -$seek, SEEK_CUR);
                    $output = ($chunk = fread($f, $seek)).$output;
                    fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                    $lines -= substr_count($chunk, "\n");
                }

                while ($lines++ < 0) {
                    $output = substr($output, strpos($output, "\n") + 1);
                }

                if (!$trace) {
                    $output = preg_replace('/ - - Trace:[^\n]*\n/s', '', $output);
                }

                fclose($f);
                echo "\n#######################################################################\n";
                echo "## \e[96mLogs - ".$files[$logs]."\e[0m";
                echo "\n#######################################################################\n";
                echo "\n".trim(($output ? $output : "\e[92mEMPTY\e[0m"))."\n";
                exit;

                break;

            case 'migrate':
                $migrateArgs = [
                    'dryrun' => (in_array('--dryrun', $args) || in_array('-d', $args) ? true : false),
                    'force' => (in_array('--force', $args) || in_array('-f', $args) ? true : false),
                    'debug' => $debug,
                ];

                $response = SpryUtilities::dbMigrate($migrateArgs);

                if (!empty($response->status) && $response->status === 'error') {
                    if (!empty($response->messages)) {
                        echo "\e[91mERROR:\e[0m\n";
                        echo implode("\n", $response->messages);
                    }
                } elseif (!empty($response->status) && $response->status === 'success') {
                    if (!empty($response->body)) {
                        echo "\e[92mSuccess!\e[0m\n";
                        echo implode("\n", $response->body);
                    }
                }

                break;

            case 'print':
                if (!$print) {
                    die("\e[91mERROR:\e[0m Missing Config Property.");
                }

                $config = Spry::config();

                if (!isset($config->$print) && strval($print) === 'codes') {
                    $print = 'responseCodes';
                }

                if (!isset($config->$print)) {
                    die("\e[91mERROR:\e[0m Config Property not found.");
                }

                $config->salt = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
                $config->db['password'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

                die(stripslashes(json_encode($config->$print, JSON_PRETTY_PRINT)));

                break;

            case 'test':
                $totalTime = 0;

                for ($i = 0; $i < $repeat; $i++) {
                    if ($singletest && (stripos($singletest, '{') !== false || (stripos($singletest, '{') === false && stripos($singletest, '*') === false))) {
                        if (stripos($singletest, '{') === false) {
                            $testdata = $singletest;

                            if (!empty(Spry::config()->tests[$testdata])) {
                                $testdata = Spry::config()->tests[$testdata];
                            }

                            echo "Running Test: ".(!empty($testdata['label']) ? $testdata['label'] : $singletest)."...\n";
                        } else {
                            $testdata = json_decode($singletest, true);

                            if (empty($testdata) || !is_array($testdata)) {
                                echo "\e[91mERROR:\e[0m Invalid Test Data.\n";

                                return false;
                            }

                            if (empty($testdata['route'])) {
                                echo "\e[91mERROR:\e[0m Test Data Missing Route.\n";

                                return false;
                            }

                            if (empty($testdata['params'])) {
                                echo "\e[91mERROR:\e[0m Test Data Missing Params.\n";

                                return false;
                            }

                            if (empty($testdata['expect'])) {
                                echo "\e[91mERROR:\e[0m Test Data Missing Expect.\n";

                                return false;
                            }

                            echo "Running Test: ".(!empty($testdata['label']) ? $testdata['label'] : $testdata['route'])."...\n";
                        }

                        $timeStart = microtime(true);
                        $response = SpryUtilities::test($testdata);
                        $time = number_format(microtime(true) - $timeStart, 6);
                        $totalTime += $time;
                        if (!empty($response->status) && $response->status === 'error') {
                            if (!empty($response->messages)) {
                                echo "\e[91mERROR:\e[0m (".$time." sec)\n";
                                echo implode("\n", $response->messages)."\n";
                            }
                        } elseif (!empty($response->status) && $response->status === 'success') {
                            if (!empty($response->body)) {
                                echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                            }
                        }

                        if ($verbose) {
                            print_r($response);
                        }
                    } else {
                        $wildcard = false;

                        if ($singletest && stripos($singletest, '*') !== false) {
                            $wildcard = str_replace('*', '', $singletest);
                        }

                        $lastResponses = [];

                        $failedTests = [];

                        if (empty(Spry::config()->tests)) {
                            $response = Spry::response(52);
                            if (!empty($response->messages)) {
                                echo "\e[91mERROR:\e[0m\n";
                                echo implode("\n", $response->messages)."\n";
                                exit;
                            }
                        }

                        foreach (Spry::config()->tests as $testName => $test) {
                            // Skip if using Wildcard with no Match
                            if ($wildcard && stripos($testName, $wildcard) === false) {
                                continue;
                            }

                            foreach (['params', 'headers'] as $property) {
                                if (!empty($test[$property])) {
                                    foreach ($test[$property] as $propertyKey => $propertyValue) {
                                        $replacements = [];

                                        preg_match_all('/\{([^\{\.]+)\.([^\}]+)\}/m', $propertyValue, $matches);

                                        if (!empty($matches[0]) && !empty($matches[1]) && !empty($matches[2]) && is_array($matches[0])) {
                                            foreach ($matches[0] as $matchKey => $match) {
                                                if (!empty($lastResponses[$matches[1][$matchKey]])) {
                                                    $replacements[$match] = SpryUtilities::extractKeyValue($matches[2][$matchKey], $lastResponses[$matches[1][$matchKey]]);
                                                }
                                            }
                                        }

                                        if (!empty($replacements)) {
                                            $test[$property][$propertyKey] = str_replace(array_keys($replacements), array_values($replacements), $test[$property][$propertyKey]);
                                        }
                                    }
                                }
                            }

                            echo "\nRunning Test: ".(!empty($test['label']) ? $test['label'] : $testName)."...\n";
                            $timeStart = microtime(true);

                            // Run Test
                            $response = SpryUtilities::test($test);

                            $time = number_format(microtime(true) - $timeStart, 6);
                            $totalTime += $time;
                            if (!empty($response->status) && $response->status === 'error') {
                                $failedTests[] = $testName;
                                if (!empty($response->messages)) {
                                    echo "\e[91mFailed:\e[0m (".$time." sec)\n";
                                    echo implode("\n", $response->messages)."\n";
                                }
                            } elseif (!empty($response->status) && $response->status === 'success') {
                                if (!empty($response->status)) {
                                    echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                                }
                            }

                            if ($verbose) {
                                print_r($response);
                            }

                            // Stop on Error if Skip is false
                            if (!empty($response->status) && $response->status === 'error' && !$skip) {
                                break;
                            }

                            $lastResponses[$testName] = (!empty($response->body['full_response']) ? $response->body['full_response'] : null);
                        }

                        if (empty($failedTests)) {
                            echo "\n\e[92mAll Tests Passed Successfully!\e[0m\n";
                        } else {
                            echo "\n\e[91mAll Failed Tests:\e[0m\n - ";
                            echo implode("\n - ", $failedTests)."\n";
                        }
                    }
                }

                if ($repeat > 1) {
                    echo "\n\e[92mTotal Time:\e[0m (".number_format($totalTime, 6)." sec)\n";
                    echo "\e[92mAverage Time:\e[0m (".number_format(($totalTime / $repeat), 6)." sec)\n";
                }

                if (!$keep) {
                    if (Spry::db()->deleteTestData()) {
                        echo "\e[92mCleared Test Data!\e[0m\n";
                    } else {
                        "\e[91mERROR:\e[0m Unknown Error Clearing Test Data.\n";
                    }
                }

                break;

            case 'up':
                echo
                    "Spry Server Running:\n".
                    " API Endpoint --------- \e[96mhttp://localhost:".$port."\e[0m\n";

                if (Spry::config()->webtoolsEnabled && Spry::config()->webtoolsEndpoint) {
                    echo " WebTools Url --------- \e[96mhttp://localhost:".$port.Spry::config()->webtoolsEndpoint."\e[0m\n";
                }

                echo "\n";
                echo "\e[37mPress Ctrl-C to quit....\e[0m";
                break;
        }
    }



    /**
     * Check if last DB Call had an Error
     *
     * @access 'public'
     *
     * @return boolean
     */
    private static function findConfig()
    {
        $files = [
            getcwd().'/config.php',
            getcwd().'/spry/config.php',
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return '';
    }
}
