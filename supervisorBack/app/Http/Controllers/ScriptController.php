<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class ScriptController extends Controller
{
    private $ip = '192.168.2.222';
    private $username = 'root';
    private $password = '123456';


    public function start(Request $request)
    {
        $command = 'sudo supervisorctl start ' . $request->input('name');

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function startAll(Request $request)
    {
        $command = 'sudo supervisorctl start all';

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function stop(Request $request)
    {
        $command = 'sudo supervisorctl stop ' . $request->input('name');

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function stopAll(Request $request)
    {
        $command = 'sudo supervisorctl stop all';

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function restart(Request $request)
    {
        $command = 'sudo supervisorctl restart ' . $request->input('name');

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function restartAll(Request $request)
    {
        $command = 'sudo supervisorctl restart all';

        $ssh = new SSH2($this->ip);

        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['error' => 'Login failed'], 401);
        }

        $output = $ssh->exec($command);

        return response()->json(['output' => $output]);
    }

    public function status(Request $request)
    {
        $command = 'sudo supervisorctl status';
    
        $ssh = new SSH2($this->ip);
        if (!$ssh->login($this->username, $this->password)) {
            return response()->json(['message' => 'Error al conectar por SSH'], 500);
        }
    
        $output = $ssh->exec($command);
    
        $lines = explode("\n", $output);
    
        $scripts = [];
        foreach ($lines as $line) {
            $words = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
    
            if (count($words) >= 2) {
                $name = $words[0];
                $status = $words[1];
                $pid = null;
                $uptime = null;
    
                if ($status == 'RUNNING') {
                    $pidIndex = array_search('pid', $words);
                    if ($pidIndex !== false && isset($words[$pidIndex + 1])) {
                        $pid = $words[$pidIndex + 1];
                    }
                    if (isset($words[$pidIndex + 3])) {
                        $uptime = $words[$pidIndex + 3];
                    }
                } else {
                    $details = array_slice($words, 2);
                    $pid = implode(' ', $details);
                }
    
                $scripts[] = [
                    'name' => $name,
                    'status' => $status,
                    'pid' => $pid,
                    'uptime' => $uptime,
                ];
            }
        }
    
        return response()->json(['message' => 'Scripts obtenidos correctamente', 'scripts' => $scripts], 200);
    }
    


    public function sendNumber(Request $request){
        $ssh = new SSH2('192.168.2.222');
    
        if (!$ssh->login('root', '123456')) {
            exit('Error de autenticación');
        }
    
        //$nuevoValor = $request->input('selectedValue');

        $rutaArchivo = '/etc/supervisord.d/script.ini';
    
        echo "Conectado al servidor SSH.\n";
    
        $valor_actual = $ssh->exec("grep -o 'script.php [0-9]*' $rutaArchivo | sed 's/script.php //'");
        echo "Valor actual: $valor_actual\n";

        //echo "Ejecutando el comando: sed -i 's/script.php $valor_actual/script.php/g' $rutaArchivo\n";

        $valor_actual = trim($valor_actual);

    
        $ssh->exec("sed -i 's/script.php $valor_actual/script.php/g' $rutaArchivo");
        echo "Comando de actualización ejecutado.\n";
    
        $nuevoValor = "Jola";
        $comandoSed = "sed -i 's/script.php\"/script.php $nuevoValor\"/g' $rutaArchivo";
        $ssh->exec($comandoSed);
        echo "Nuevo valor establecido: $nuevoValor\n";
    
        $ssh->exec("sudo supervisorctl update");
        echo "Actualización de supervisorctl completada.\n";
    }
    
        public function downloadLogs(Request $request)
        {
            $sftp = new SFTP($this->ip);
    
            if (!$sftp->login($this->username, $this->password)) {
                return response()->json(['error' => 'Login failed'], 500);
            }

            $directorio = $request->input('name');
    
            $remoteDir = "/var/log/supervisor/$directorio";

            echo $remoteDir;
            
    
            $remoteDir = "/var/log/supervisor/$directorio";
            $localDir = storage_path("app/supervisor_logs/$directorio");
    
            if (!is_dir($localDir)) {
                mkdir($localDir, 0755, true);
            }
    
            $files = $sftp->nlist($remoteDir);
    
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
    
                $remoteFile = $remoteDir . '/' . $file;
                $localFile = $localDir . '/' . $file;
    
                if ($sftp->get($remoteFile, $localFile)) {
                    echo "Downloaded: $file\n";
                } else {
                    echo "Failed to download: $file\n";
                }
            }
    
            return response()->json(['success' => 'Logs downloaded successfully']);
        }
        
        public function showLog(Request $request)
        {
            $ssh = new SSH2($this->ip);

            $directorio = $request->input('name');

            
            if (!$ssh->login($this->username, $this->password)) {
                return response()->json(['error' => 'Login failed'], 500);
            }
    
            $logContent = $ssh->exec("cat /var/log/supervisor/$directorio/script.out.log");
    
            return response()->json(['log' => $logContent]);
        }

}


/* public function downloadScript(Request $request)
    {
        $scriptPath = '/etc/supervisord.d/script.ini';
        $scriptContent = $this->fetchScriptContent($scriptPath);

        if (!$scriptContent) {
            return response()->json(['message' => 'Error al descargar el archivo'], 500);
        }

        return response()->json(['message' => 'Archivo descargado correctamente', 'script' => $scriptContent], 200);
    }

    private function fetchScriptContent($path)
    {
        $ssh = new SSH2($this->ip);
        if (!$ssh->login($this->username, $this->password)) {
            return false;
        }

        return $ssh->exec("cat $path");
    }

    

       public function sendNumber(){
        $ssh = new SSH2('192.168.2.222');
    
        if (!$ssh->login('root', '123456')) {
            exit('Error de autenticación');
        }
    
        $rutaArchivo = '/etc/supervisord.d/script.ini';
    
        echo "Conectado al servidor SSH.\n";
    
        $valor_actual = $ssh->exec("grep -o 'script.php [0-9]*' $rutaArchivo | sed 's/script.php //'");
        echo "Valor actual: $valor_actual\n";

        //echo "Ejecutando el comando: sed -i 's/script.php $valor_actual/script.php/g' $rutaArchivo\n";

        $valor_actual = trim($valor_actual);

    
        $ssh->exec("sed -i 's/script.php $valor_actual/script.php/g' $rutaArchivo");
        echo "Comando de actualización ejecutado.\n";
    
        $nuevoValor = "3";
        $comandoSed = "sed -i 's/script.php\"/script.php $nuevoValor\"/g' $rutaArchivo";
        $ssh->exec($comandoSed);
        echo "Nuevo valor establecido: $nuevoValor\n";
    
        $ssh->exec("sudo supervisorctl update");
        echo "Actualización de supervisorctl completada.\n";
    }