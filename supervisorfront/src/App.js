import React, { useState, useEffect } from 'react';
import './App.css';
import { toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const App = () => {
  const [scripts, setScripts] = useState([]);
  const [selectedValue, setSelectedValue] = useState('');

  useEffect(() => {
    fetchScripts();
    const interval = setInterval(fetchScripts, 60000);//3000 creo que es mejor cada 3 segundos o un poquito mas

    return () => clearInterval(interval);
  }, []);

  const fetchScripts = async () => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/scripts/status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      const data = await response.json();
      setScripts(data.scripts);
    } catch (error) {
      console.error('Error fetching scripts:', error);
    }
  };


  const handleCheckboxChange = (event) => {
    setSelectedValue(event.target.value);
  };

  const handleSubmit = async () => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/sendNumber', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ selectedValue })
          
      });
      console.log('Número enviado:', selectedValue);
    } catch (error) {
      console.error('Error enviando número:', error);
    }
  };
  


  const handleRestart = async (name) => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/restart', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
      });
      fetchScripts();
    } catch (error) {
      console.error('Error restarting script:', error);
    }
  };

  const handleStart = async (name) => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/start', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
      });
      fetchScripts();
    } catch (error) {
      console.error('Error starting script:', error);
    }
  };

  const handleStop = async (name) => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/stop', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
      });
      fetchScripts();
    } catch (error) {
      console.error('Error stopping script:', error);
    }
  };

  const handleStopAll = async () => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/stop/all', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      fetchScripts();
    } catch (error) {
      console.error('Error stopping all scripts:', error);
    }
  };

  const handleStartAll = async () => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/start/all', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      fetchScripts();
    } catch (error) {
      console.error('Error starting all scripts:', error);
    }
  };

  const handleRestartAll = async () => {
    try {
      await fetch('http://127.0.0.1:8000/api/scripts/restart/all', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      fetchScripts();
    } catch (error) {
      console.error('Error restarting all scripts:', error);
    }
  };

  const handleDownloadLogs = async (name) => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/download-logs', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
      });
  
      if (!response.ok) {
        throw new Error('Error en la respuesta de la API');
      }
  
      fetchScripts();
      toast.success("Descargado correctamente");
    } catch (error) {
      toast.error("Error al descargar los logs");
      console.error('Error al descargar los logs:', error);
    }
  };

  const handleShowLog = async (name) => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/show-log', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
      });
  
      if (!response.ok) {
        throw new Error('Error en la respuesta de la API');
      }
  
      fetchScripts();
      toast.success("Descargado correctamente");
    } catch (error) {
      toast.error("Error al descargar los logs");
      console.error('Error al descargar los logs:', error);
    }
  };
  

    const isDate = (pid) => {
      return /^[A-Za-z]{3} \d{2} \d{2}:\d{2} [AP]M$/.test(pid);
    };

  return (
    
    <div className="app-container">
      <h1>Scripts</h1>
      
      <a href="sftp://192.168.2.222/etc/supervisord.d/script.ini">Modificar ini</a>

      <ul className="scripts-list">
        <button className="action-btn restart-btn" onClick={() => handleStopAll()}>Detener todos</button>
        <button className="action-btn restart-btn" onClick={() => handleStartAll()}>Iniciar todos</button>
        <button className="action-btn restart-btn" onClick={() => handleRestartAll()}>Reiniciar todos</button>

        {scripts.map((script, index) => (
          <li key={index} className="script-item">
            <div className="script-info">
              <span className="script-name">{script.name}</span>
              <span className="script-status">{script.status}</span>&nbsp; &nbsp; 
              {!isDate(script.pid) && (
                <>
                  <span className="script-status"><b>PID:</b>{script.pid}</span>&nbsp; &nbsp; 
                  <span className="script-status"><b>UPTIME:</b>{script.uptime}</span>
                </>
              )}
            </div>
            <div className="script-actions">
              <button className="action-btn restart-btn" onClick={() => handleRestart(script.name)}>Restart</button>
              <button className="action-btn start-btn" onClick={() => handleStart(script.name)}>Start</button>
              <button className="action-btn stop-btn" onClick={() => handleStop(script.name)}>Stop</button>
              <button className="action-btn" onClick={() => handleDownloadLogs(script.name)}>Descargar logs</button>
              <button className="action-btn" onClick={() => handleShowLog(script.name)}>Ver logs</button>
            </div>
            
          </li>
        ))}
      </ul>
      <div className="checkbox-container">
        <label>
          <input type="checkbox" value="1" onChange={handleCheckboxChange} />
          Opción 1
        </label>
        <label>
          <input type="checkbox" value="2" onChange={handleCheckboxChange} />
          Opción 2
        </label>
        <label>
          <input type="checkbox" value="3" onChange={handleCheckboxChange} />
          Opción 3
        </label>
      </div>
      <button onClick={handleSubmit}>Enviar Número</button>
    </div>
  );
};

export default App;
