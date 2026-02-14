import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import api from '../api';

const Download = () => {
  const { token } = useParams();
  const [downloading, setDownloading] = useState(false);
  const [error, setError] = useState('');

  const handleDownload = async () => {
    setDownloading(true);
    try {
      const response = await api.get(`/files/download/${token}`, {
        responseType: 'blob',
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      
      const contentDisposition = response.headers['content-disposition'];
      let filename = 'downloaded-file';
      if (contentDisposition) {
          const matches = /filename="([^"]*)"/.exec(contentDisposition);
          if (matches != null && matches[1]) { 
              filename = matches[1];
          }
      }
      
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err) {
      console.error(err);
      setError('File expired or not found');
    } finally {
      setDownloading(false);
    }
  };

  return (
    <motion.div 
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.4 }}
      style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '80vh' }}
    >
      <div className="card" style={{ maxWidth: '400px', width: '100%', textAlign: 'center', padding: '40px' }}>
        <h2 style={{ marginBottom: '30px', fontSize: '2rem' }}>Download File</h2>
        {error ? (
          <div style={{ color: '#ff4757', background: 'rgba(255, 71, 87, 0.1)', padding: '15px', borderRadius: '8px' }}>{error}</div>
        ) : (
          <div>
            <p style={{ marginBottom: '30px', fontSize: '1.1rem', color: 'var(--text-secondary)' }}>Your file is ready for download.</p>
            <div style={{ background: 'rgba(255,255,255,0.05)', padding: '15px', borderRadius: '8px', marginBottom: '30px', fontSize: '0.9rem', color: '#888' }}>
              ⚠️ This file might be deleted automatically after download.
            </div>
            <button onClick={handleDownload} className="btn-primary" disabled={downloading} style={{ width: '100%', padding: '15px' }}>
              {downloading ? 'Downloading...' : 'Download Now'}
            </button>
          </div>
        )}
      </div>
    </motion.div>
  );
};

export default Download;
