<?php

namespace Spry\SpryConnector;

use Spry\Spry;
use Spry\SpryUtilities;

// Setup Server Vars for CLI
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

class SpryCliConnector
{

    private static $cli_path = '';

    private static function find_config()
    {
        $files = [
            getcwd().'/config.php',
            getcwd().'/spry/config.php'
        ];

        foreach($files as $file)
        {
            if(file_exists($file))
            {
                return $file;
            }
        }

        return '';
    }

    public static function run($cli_path='')
    {
        self::$cli_path = $cli_path;

        $args = [];
        $config_file = '';
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
        $verbose = false;
        $skip = false;
        $repeat = 1;
        $port = 8000;
        $logs = '';
        $lines = '100';
		$print = '';
        $trace = false;
        $with_routes = false;
        $with_codes = false;
        $with_tests = false;
        $with_all = false;
		$code_gap = 9;
		$keep = false;

        if(!empty($_SERVER['argv']))
        {
            $args = $_SERVER['argv'];
            $key = array_search('--config', $args);
            if($key !== false && isset($args[($key + 1)]))
            {
                $config_file = $args[($key + 1)];
            }

            $key = array_search('--verbose', $args);
            if($key !== false)
            {
                $verbose = true;
            }

			$key = array_search('--skip', $args);
            if($key !== false)
            {
                $skip = true;
            }

			$key = array_search('--keep', $args);
            if($key !== false)
            {
                $keep = true;
            }

            $key = array_search('h', $args);
            if($key === false)
            {
                $key = array_search('hash', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $hash = $args[($key + 1)];
            }

			$key = array_search('p', $args);
            if($key === false)
            {
                $key = array_search('print', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $print = $args[($key + 1)];
            }

            $key = array_search('u', $args);
            if($key === false)
            {
                $key = array_search('up', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $port = $args[($key + 1)];
            }

            $key = array_search('t', $args);
            if($key === false)
            {
                $key = array_search('test', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $singletest = $args[($key + 1)];
            }

            $key = array_search('c', $args);
            if($key === false)
            {
                $key = array_search('component', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $component = $args[($key + 1)];
            }

            $key = array_search('clear', $args);
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $clear = $args[($key + 1)];
            }

            $key = array_search('--repeat', $args);
            if($key !== false && isset($args[($key + 1)]))
            {
                if(is_numeric($args[($key + 1)]))
                {
                    $repeat = floor($args[($key + 1)]);
                }
            }

            $key = array_search('l', $args);
            if($key === false)
            {
                $key = array_search('log', $args);
            }
            if($key === false)
            {
                $key = array_search('logs', $args);
            }
            if($key !== false && isset($args[($key + 1)]) && strpos($args[($key + 1)], '--') === false)
            {
                $logs = $args[($key + 1)];
            }

            $key = array_search('--lines', $args);
            if($key !== false && isset($args[($key + 1)]))
            {
                if(is_numeric($args[($key + 1)]))
                {
                    $lines = floor($args[($key + 1)]);
                }
            }

			$key = array_search('-cg', $args);
            if($key === false)
            {
                $key = array_search('--code-gap', $args);
            }
            if($key !== false && isset($args[($key + 1)]))
            {
                if(is_numeric($args[($key + 1)]))
                {
                    $code_gap = floor($args[($key + 1)]);
                }
            }

            $key = array_search('--trace', $args);
            if($key !== false)
            {
                $trace = true;
            }

            if(array_search('--with-routes', $args) !== false)
            {
                $with_routes = true;
            }

            if(array_search('--with-codes', $args) !== false)
            {
                $with_codes = true;
            }

            if(array_search('--with-tests', $args) !== false)
            {
                $with_tests = true;
            }

            if(array_search('--with-all', $args) !== false || array_search('-wa', $args) !== false)
            {
                $with_all = true;
            }

            foreach ($args as $value)
            {
                if(empty($command))
                {
                    if(in_array($value, $commands))
                    {
                        $command = $value;
                    }
                    elseif(in_array($value, array_keys($commands)))
                    {
                        $command = $commands[$value];
                    }
                }
            }
        }

        if(!$command)
        {
            if(array_search('-v', $args) !== false || array_search('--version', $args) !== false)
            {
                $command = 'version';
            }

            if(array_search('-h', $args) !== false || array_search('--help', $args) !== false)
            {
                $command = 'help';
            }
        }

        if(!$command)
        {
            die("Spry -v ".Spry::get_version()."\n\e[91mERROR:\e[0m Spry - Command not Found. For help try 'spry --help'");
        }

        if($command === 'version')
        {
            die("Spry -v ".Spry::get_version());
        }

        if($command === 'help')
        {
            echo "Spry -v ".Spry::get_version()."\n".
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
            "  --force   |  -f             - Delete Fields, Tables and other data that does not match the new Scheme.\n\n".
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

        if(!$config_file)
        {
            $config_file = self::find_config();
        }

        if(!$config_file || !file_exists($config_file))
        {
            die("\e[91mERROR:\e[0m No Config File Found. Run SpryCli from the same folder that contains your 'config.php' file or specify the config file with --config");
        }

		// Load the Main Config Data and Set Autoloader and Configure Filters
		Spry::configure($config_file);

        switch($command)
        {
            case 'component':

                $component_sanitized = preg_replace("/\W/", '', str_replace([' ', '-'], '_', $component));
                $component_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $component_sanitized)));

                if(!$component_name)
                {
                    die("\e[91mERROR:\e[0m Missing Component Name.");
                }

                $source_component = self::$cli_path.'/example_project/components/example.php';
                $new_component = Spry::config()->components_dir.'/'.$component_name.'.php';

                if(!is_dir(Spry::config()->components_dir.'/'))
                {
                    die("\e[91mERROR:\e[0m Component Directory is not configured in config.php or not found.");
                }

                if(!is_writable(Spry::config()->components_dir.'/'))
                {
                    die("\e[91mERROR:\e[0m Component Directory Does not seem to be writable.");
                }

                if(file_exists($new_component))
                {
                    die("\e[91mERROR:\e[0m Component with that name already exists.");
                }

                if(!file_exists($source_component))
                {
                    die("\e[91mERROR:\e[0m Missing Source Component Template.");
                }

                if(!copy($source_component, $new_component))
                {
                    die("\e[91mERROR:\e[0m Component could not be created.");
                }

                // Replace Component config_content
                $component_contents = file_get_contents($new_component);
                $component_contents = str_replace('class Example', 'class '.$component_name, $component_contents);
                $component_contents = str_replace('examples_table', SpryUtilities::plural(strtolower($component_sanitized)), $component_contents);
                file_put_contents($new_component, $component_contents);

				if($with_codes || $with_all)
				{
					if(!empty(Spry::config()->response_codes))
					{
						if($code_keys = array_keys(Spry::config()->response_codes))
						{
							sort($code_keys);
							$last_code = intval(substr(strval(end($code_keys)), 1));

							if($last_code)
							{
								// Increase by one to make sure it doesn't
								// overlap when --code-gap is set to 0
								$last_code++;

								if($last_code < 100)
								$last_code = 110;

								$new_output = "\n\t// " . $component_name . "\n";
								$new_output.= "\t2" . ($last_code + $code_gap) . " => ['en' => 'Successfully Retrieved " . SpryUtilities::single($component_name) . "'],\n";
								$new_output.= "\t4" . ($last_code + $code_gap) . " => ['en' => 'No " . SpryUtilities::single($component_name) . " with that ID Found'],\n";
								$new_output.= "\t5" . ($last_code + $code_gap) . " => ['en' => 'Error: Retrieving " . SpryUtilities::single($component_name) . "'],\n";

								$new_output.= "\t2" . ($last_code + $code_gap + 1) . " => ['en' => 'Successfully Retrieved " . SpryUtilities::plural($component_name) . "'],\n";
								$new_output.= "\t4" . ($last_code + $code_gap + 1) . " => ['en' => 'No " . SpryUtilities::plural($component_name) . " Found'],\n";
								$new_output.= "\t5" . ($last_code + $code_gap + 1) . " => ['en' => 'Error: Retrieving " . SpryUtilities::plural($component_name) . "'],\n";

								$new_output.= "\t2" . ($last_code + $code_gap + 2) . " => ['en' => 'Successfully Created " . SpryUtilities::single($component_name) . "'],\n";
								$new_output.= "\t5" . ($last_code + $code_gap + 2) . " => ['en' => 'Error: Creating " . SpryUtilities::single($component_name) . "'],\n";

								$new_output.= "\t2" . ($last_code + $code_gap + 3) . " => ['en' => 'Successfully Updated " . SpryUtilities::single($component_name) . "'],\n";
								$new_output.= "\t4" . ($last_code + $code_gap + 3) . " => ['en' => 'No " . SpryUtilities::single($component_name) . " with that ID Found'],\n";
								$new_output.= "\t5" . ($last_code + $code_gap + 3) . " => ['en' => 'Error: Updating " . SpryUtilities::single($component_name) . "'],\n";

								$new_output.= "\t2" . ($last_code + $code_gap + 4) . " => ['en' => 'Successfully Deleted " . SpryUtilities::single($component_name) . "'],\n";
								$new_output.= "\t5" . ($last_code + $code_gap + 4) . " => ['en' => 'Error: Deleting " . SpryUtilities::single($component_name) . "'],\n];";

								// Update Component Codes
								$component_contents = preg_replace('/000/', ($last_code + $code_gap), $component_contents, 1);
								$component_contents = preg_replace('/000/', ($last_code + $code_gap + 1), $component_contents, 1);
								$component_contents = preg_replace('/000/', ($last_code + $code_gap + 2), $component_contents, 1);
								$component_contents = preg_replace('/000/', ($last_code + $code_gap + 3), $component_contents, 1);
								$component_contents = preg_replace('/000/', ($last_code + $code_gap + 4), $component_contents, 1);
								if(file_put_contents($new_component, $component_contents))
								{
									// Add Codes to Config file
					                $config_contents = file_get_contents($config_file);

									$pos_start = stripos($config_contents, '$config->response_codes');
									if($pos_start !== false)
									{
										if($pos = stripos($config_contents, '];', $pos_start))
										{
											$new_config_contents = substr($config_contents, 0, $pos) . $new_output . substr($config_contents, ($pos + 2));
											file_put_contents($config_file, $new_config_contents);
										}
									}
								}
							}
						}
					}
				}

				if($with_routes || $with_all)
				{
					$new_output = "\n\t// " . $component_name . "\n";

					$new_output.= "\t'/" . strtolower($component_sanitized) . "/get' => [\n";
						$new_output.= "\t\t'label' => 'Get " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'controller' => '" . $component_name . "::get',\n";
						$new_output.= "\t\t'access' => 'public',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => [\n";
								$new_output.= "\t\t\t\t'required' => true,\n";
								$new_output.= "\t\t\t\t'int' => true\n";
							$new_output.= "\t\t\t]\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'/" . strtolower($component_sanitized) . "/get_all' => [\n";
						$new_output.= "\t\t'label' => 'Get All " . SpryUtilities::plural($component_name) . "',\n";
						$new_output.= "\t\t'controller' => '" . $component_name . "::get_all',\n";
						$new_output.= "\t\t'access' => 'public',\n";
						$new_output.= "\t\t'params' => []\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'/" . strtolower($component_sanitized) . "/insert' => [\n";
						$new_output.= "\t\t'label' => 'Insert " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'controller' => '" . $component_name . "::insert',\n";
						$new_output.= "\t\t'access' => 'public',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'name' => [\n";
								$new_output.= "\t\t\t\t'required' => true,\n";
								$new_output.= "\t\t\t\t'minlength' => 1\n";
							$new_output.= "\t\t\t],\n";
							$new_output.= "\t\t\t'email' => [\n";
								$new_output.= "\t\t\t\t'required' => true,\n";
								$new_output.= "\t\t\t\t'email' => true\n";
							$new_output.= "\t\t\t]\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'/" . strtolower($component_sanitized) . "/update' => [\n";
						$new_output.= "\t\t'label' => 'Update " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'controller' => '" . $component_name . "::update',\n";
						$new_output.= "\t\t'access' => 'public',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => [\n";
								$new_output.= "\t\t\t\t'required' => true,\n";
								$new_output.= "\t\t\t\t'int' => true\n";
							$new_output.= "\t\t\t],\n";
							$new_output.= "\t\t\t'name' => [\n";
								$new_output.= "\t\t\t\t'minlength' => 1\n";
							$new_output.= "\t\t\t],\n";
							$new_output.= "\t\t\t'email' => [\n";
								$new_output.= "\t\t\t\t'email' => true\n";
							$new_output.= "\t\t\t]\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'/" . strtolower($component_sanitized) . "/delete' => [\n";
						$new_output.= "\t\t'label' => 'Delete " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'controller' => '" . $component_name . "::delete',\n";
						$new_output.= "\t\t'access' => 'public',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => [\n";
								$new_output.= "\t\t\t\t'required' => true,\n";
								$new_output.= "\t\t\t\t'int' => true\n";
							$new_output.= "\t\t\t]\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n];";

					// Add Codes to Config file
					$config_contents = file_get_contents($config_file);

					$pos_start = stripos($config_contents, '$config->routes');
					if($pos_start !== false)
					{
						if($pos = stripos($config_contents, '];', $pos_start))
						{
							$new_config_contents = substr($config_contents, 0, $pos) . $new_output . substr($config_contents, ($pos + 2));
							file_put_contents($config_file, $new_config_contents);
						}
					}
				}

				if($with_tests || $with_all)
				{
					$new_output = "\n\t// " . $component_name . "\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_get_all_empty' => [\n";
						$new_output.= "\t\t'label' => 'Get All " . SpryUtilities::plural($component_name) . " Empty',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/get_all',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'name' => '!'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 4" . (!empty($last_code) ? ($last_code + $code_gap + 1) : '4000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_insert' => [\n";
						$new_output.= "\t\t'label' => 'Insert " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/insert',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'name' => 'Bob',\n";
							$new_output.= "\t\t\t'email' => 'bob'.time().'@gmail.com'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 2" . (!empty($last_code) ? ($last_code + $code_gap + 2) : '2000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_get_all' => [\n";
						$new_output.= "\t\t'label' => 'Get All " . SpryUtilities::plural($component_name) . "',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/get_all',\n";
						$new_output.= "\t\t'params' => [],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 2" . (!empty($last_code) ? ($last_code + $code_gap + 1) : '2000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_get' => [\n";
						$new_output.= "\t\t'label' => 'Get " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/get',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => '{{body.id}}'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 2" . (!empty($last_code) ? ($last_code + $code_gap) : '2000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_get_empty' => [\n";
						$new_output.= "\t\t'label' => 'Get " . SpryUtilities::single($component_name) . " Empty',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/get',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => '-1'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 4" . (!empty($last_code) ? ($last_code + $code_gap) : '4000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_update' => [\n";
						$new_output.= "\t\t'label' => 'Update " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/update',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => '{{body.id}}',\n";
							$new_output.= "\t\t\t'name' => 'Bob Bobby',\n";
							$new_output.= "\t\t\t'email' => 'bob'.time().'@gmail.com'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 2" . (!empty($last_code) ? ($last_code + $code_gap + 3) : '2000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n";

					$new_output.= "\t'" . strtolower($component_sanitized) . "_delete' => [\n";
						$new_output.= "\t\t'label' => 'Delete " . SpryUtilities::single($component_name) . "',\n";
						$new_output.= "\t\t'route' => '/" . strtolower($component_sanitized) . "/delete',\n";
						$new_output.= "\t\t'params' => [\n";
							$new_output.= "\t\t\t'id' => '{body.id}'\n";
						$new_output.= "\t\t],\n";
						$new_output.= "\t\t'expect' => [\n";
							$new_output.= "\t\t\t'code' => 2" . (!empty($last_code) ? ($last_code + $code_gap + 4) : '2000') . ",\n";
						$new_output.= "\t\t]\n";
					$new_output.= "\t],\n];";

					// Add Codes to Config file
					$config_contents = file_get_contents($config_file);

					$pos_start = stripos($config_contents, '$config->tests');
					if($pos_start !== false)
					{
						if($pos = stripos($config_contents, '];', $pos_start))
						{
							$new_config_contents = substr($config_contents, 0, $pos) . $new_output . substr($config_contents, ($pos + 2));
							file_put_contents($config_file, $new_config_contents);
						}
					}
				}

                echo "\n\e[92mComponent Created Successfully!\e[0m\n".$new_component."\n";

            break;

            case 'clear':

                if(!$clear)
                {
                    die("\e[91mERROR:\e[0m Clear Object Missing.\n");
                }

                switch($clear)
                {
                    case 'logs':

                        if(!empty(Spry::config()->log_api_file) && file_exists(Spry::config()->log_api_file))
                        {
                            if(file_put_contents(Spry::config()->log_api_file, '') !== false)
                            {
                                echo "\e[92mCleared API Logs!\e[0m\n";
                            }
                            else
                            {
                                "\e[91mUnknown ERROR:\e[0m Clearing API Log.\n";
                            }
                        }
                        else
                        {
                            "\e[91mERROR:\e[0m Could not find API Log.\n";
                        }

                        if(!empty(Spry::config()->log_php_file) && file_exists(Spry::config()->log_php_file))
                        {
                            if(file_put_contents(Spry::config()->log_php_file, '') !== false)
                            {
                                echo "\e[92mCleared PHP Logs!\e[0m\n";
                            }
                            else
                            {
                                "\e[91mUnknown ERROR:\e[0m Clearing PHP Log.\n";
                            }
                        }
                        else
                        {
                            "\e[91mERROR:\e[0m Could not find PHP Log.\n";
                        }

                    break;

					case 'tests':

						if(Spry::db()->deleteTestData())
						{
							echo "\e[92mCleared Test Data!\e[0m\n";
						}
						else
						{
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

                if(is_writable($config_file) && is_readable($config_file))
                {
                    $salt = sha1(rand(10000,99999).uniqid(mt_rand(), true).rand(10000,99999));
                    //echo $salt;
                    $config_contents = str_replace("config->salt = '';", "config->salt = '".$salt."';", file_get_contents($config_file));
                    if($config_contents)
                    {
                        if(file_put_contents($config_file, $config_contents))
                        {
                            echo "Salt value auto generated.\n";
                        }
                        else
                        {
                            echo "\e[91mERROR:\e[0m Could not update config file salt value.\n";
                        }

                        echo "Update the rest of your config file accordingly: ".$config_file."\n";
                    }
                }

                exit;

            break;

            case 'hash':

                if(!$hash)
                {
                    die("\e[91mERROR:\e[0m Missing Hash Value.  If hashing a value that has spaces then wrap with \"\"");
                }

                die(SpryUtilities::hash($hash));

            break;

            case 'logs':

                if(empty($logs) || !in_array($logs, ['php', 'api']))
                {
                    die("\e[91mERROR:\e[0m Missing Logs Value.  Either 'php' or 'api' is acceptable.");
                }

                $files = [
                    'php' => Spry::config()->log_php_file,
                    'api' => Spry::config()->log_api_file,
                ];

                if(!file_exists($files[$logs]))
                {
                    die("\e[91mERROR:\e[0m Cannot find Logs File (".(!empty($files[$logs]) ? $files[$logs] : '')."). Check your Configuration for correct settings.");
                }

                $f = fopen($files[$logs], "rb");
                if($f === false)
                {
                    die("\e[91mERROR:\e[0m Could not read file (".(!empty($files[$logs]) ? $files[$logs] : '')."). Check the Permissions.");
                }

                $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
                fseek($f, -1, SEEK_END);

                if(fread($f, 1) != "\n")
                {
                    $lines -= 1;
                }

                $output = '';
                $chunk = '';

                while (ftell($f) > 0 && $lines >= 0)
                {
                    $seek = min(ftell($f), $buffer);
                    fseek($f, -$seek, SEEK_CUR);
                    $output = ($chunk = fread($f, $seek)) . $output;
                    fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                    $lines -= substr_count($chunk, "\n");
                }

                while ($lines++ < 0)
                {
                    $output = substr($output, strpos($output, "\n") + 1);
                }

                if(!$trace)
                {
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

                $migrate_args = [
                    'dryrun' => (in_array('--dryrun', $args) || in_array('-d', $args) ? true : false),
                    'force' => (in_array('--force', $args) || in_array('-f', $args) ? true : false),
                ];

                $response = SpryUtilities::dbMigrate($migrate_args);

                if(!empty($response['status']) && $response['status'] === 'error')
                {
                    if(!empty($response['messages']))
                    {
                        echo "\e[91mERROR:\e[0m\n";
                        echo implode("\n", $response['messages']);
                    }
                }
                elseif(!empty($response['status']) && $response['status'] === 'success')
                {
                    if(!empty($response['body']))
                    {
                        echo "\e[92mSuccess!\e[0m\n";
                        echo implode("\n", $response['body']);
                    }
                }

            break;

			case 'print':

                if(!$print)
                {
                    die("\e[91mERROR:\e[0m Missing Config Property.");
                }

				$config = Spry::config();

				if(!isset($config->$print) && $print === 'codes')
                {
					$print = 'response_codes';
				}

				if(!isset($config->$print))
                {
                    die("\e[91mERROR:\e[0m Config Property not found.");
                }

				$config->salt = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
				$config->db['password'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

                die(stripslashes(json_encode($config->$print, JSON_PRETTY_PRINT)));

            break;

            case 'test':

                $total_time = 0;

                for($i=0; $i < $repeat; $i++)
                {
                    if($singletest && (stripos( $singletest, '{' ) !== false || (stripos( $singletest, '{' ) === false && stripos( $singletest, '*' ) === false)))
                    {
                        if(stripos( $singletest, '{' ) === false)
                        {
                            $testdata = $singletest;

                            if(!empty(Spry::config()->tests[$testdata]))
                            {
                                $testdata = Spry::config()->tests[$testdata];
                            }

                            echo "Running Test: ".(!empty($testdata['label']) ? $testdata['label'] : $singletest)."...\n";

                        }
                        else
                        {
                            $testdata = json_decode($singletest, true);

                            if(empty($testdata) || !is_array($testdata))
                            {
                                echo "\e[91mERROR:\e[0m Invalid Test Data.\n";
                                return false;
                            }

                            if(empty($testdata['route']))
                            {
                                echo "\e[91mERROR:\e[0m Test Data Missing Route.\n";
                                return false;
                            }

                            if(empty($testdata['params']))
                            {
                                echo "\e[91mERROR:\e[0m Test Data Missing Params.\n";
                                return false;
                            }

                            if(empty($testdata['expect']))
                            {
                                echo "\e[91mERROR:\e[0m Test Data Missing Expect.\n";
                                return false;
                            }

                            echo "Running Test: ".(!empty($testdata['label']) ? $testdata['label'] : $testdata['route'])."...\n";

                        }

                        $time_start = microtime(true);
                        $response = SpryUtilities::test($testdata);
                        $time = number_format(microtime(true) - $time_start, 6);
                        $total_time+= $time;
                        if(!empty($response['status']) && $response['status'] === 'error')
                        {
                            if(!empty($response['messages']))
                            {
                                echo "\e[91mERROR:\e[0m (".$time." sec)\n";
                                echo implode("\n", $response['messages'])."\n";
                            }
                        }
                        elseif(!empty($response['status']) && $response['status'] === 'success')
                        {
                            if(!empty($response['body']))
                            {
                                echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                            }
                        }

                        if($verbose)
                        {
                            print_r($response);
                        }
                    }
                    else
                    {
						$wildcard = false;

						if($singletest && stripos($singletest, '*' ) !== false)
						{
							$wildcard = str_replace('*', '', $singletest);
						}

                        $last_last_response = null;
                        $last_response = null;

                        $failed_tests = [];

                        if(empty(Spry::config()->tests))
                        {
                            $response = Spry::results(5052, null);
                            if(!empty($response['messages']))
                            {
                                echo "\e[91mERROR:\e[0m\n";
                                echo implode("\n", $response['messages'])."\n";
                                exit;
                            }
                        }

                        foreach (Spry::config()->tests as $test_name => $test)
                        {
							// Skip if using Wildcard with no Match
							if($wildcard && stripos( $test_name, $wildcard ) === false)
							{
								continue;
							}

                            foreach ($test['params'] as $param_key => $param)
                			{
                                if(!empty($last_last_response) && substr($param, 0, 2) === '{{' && substr($param, -2) === '}}')
                                {
                                    $test['params'][$param_key] = SpryUtilities::extractKeyValue(substr($param, 2, -2), $last_last_response);
                                }
								else if(!empty($last_response) && substr($param, 0, 1) === '{' && substr($param, -1) === '}')
                                {
                                    $test['params'][$param_key] = SpryUtilities::extractKeyValue(substr($param, 1, -1), $last_response);
                                }
                			}

                            echo "\nRunning Test: ".(!empty($test['label']) ? $test['label'] : $test_name)."...\n";
                            $time_start = microtime(true);
                            $response = SpryUtilities::test($test);
                            $time = number_format(microtime(true) - $time_start, 6);
                            $total_time+= $time;
                            if(!empty($response['status']) && $response['status'] === 'error')
                            {
                                $failed_tests[] = $test_name;
                                if(!empty($response['messages']))
                                {
                                    echo "\e[91mFailed:\e[0m (".$time." sec)\n";
                                    echo implode("\n", $response['messages'])."\n";
                                }
                            }
                            elseif(!empty($response['status']) && $response['status'] === 'success')
                            {
                                if(!empty($response['body']))
                                {
                                    echo "\e[92mSuccess!\e[0m (".$time." sec)\n";
                                }
                            }

                            if($verbose)
                            {
                                print_r($response);
                            }

							// Stop on Error if Skip is false
							if(!empty($response['status']) && $response['status'] === 'error' && !$skip)
                            {
								break;
							}

							$last_last_response = $last_response;
                            $last_response = (!empty($response['body']['full_response']) ? $response['body']['full_response'] : null);
                        }

                        if(empty($failed_tests))
                        {
                            echo "\n\e[92mAll Tests Passed Successfully!\e[0m\n";
                        }
                        else
                        {
                            echo "\n\e[91mAll Failed Tests:\e[0m\n - ";
                            echo implode("\n - ", $failed_tests)."\n";
                        }
                    }
                }

                if($repeat > 1)
                {
                    echo "\n\e[92mTotal Time:\e[0m (".number_format($total_time, 6)." sec)\n";
                    echo "\e[92mAverage Time:\e[0m (".number_format(($total_time/$repeat), 6)." sec)\n";
                }

				if(!$keep)
				{
					if(Spry::db()->deleteTestData())
					{
						echo "\e[92mCleared Test Data!\e[0m\n";
					}
					else
					{
						"\e[91mERROR:\e[0m Unknown Error Clearing Test Data.\n";
					}
				}

            break;

            case 'up':

                echo
                "Spry Server Running:\n".
                " API Endpoint --------- \e[96mhttp://localhost:".$port."\e[0m\n";

                if(Spry::config()->webtools_enabled && Spry::config()->webtools_endpoint )
                {
                    echo " WebTools Url --------- \e[96mhttp://localhost:".$port.Spry::config()->webtools_endpoint."\e[0m\n";
                }

                echo "\n";
                echo "\e[37mPress Ctrl-C to quit....\e[0m";
            break;
        }
    }
}
