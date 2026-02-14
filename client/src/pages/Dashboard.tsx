import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import api from '../api';

const Dashboard = () => {
  const [file, setFile] = useState<File | null>(null);
  const [myFiles, setMyFiles] = useState<any[]>([]);
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [expiryDays, setExpiryDays] = useState(1);

  const fetchFiles = async () => {
    try {
      const res = await api.get('/files/my-files');
      setMyFiles(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    fetchFiles();
  }, []);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setFile(e.target.files[0]);
    }
  };

  const handleUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('expiry_days', expiryDays.toString());

    setUploading(true);
    try {
      await api.post('/files/upload', formData, {
        onUploadProgress: (progressEvent) => {
          const progress = progressEvent.total ? Math.round((progressEvent.loaded * 100) / progressEvent.total) : 0;
          setUploadProgress(progress);
        }
      });
      setFile(null);
      setUploadProgress(0);
      fetchFiles();
    } catch (err) {
      console.error(err);
      alert('Upload failed');
    } finally {
      setUploading(false);
    }
  };

  const copyLink = (token: string) => {
    const url = `${window.location.origin}/download/${token}`;
    navigator.clipboard.writeText(url);
    alert('Link copied to clipboard');
  };

  return (
    <motion.div 
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
      style={{ maxWidth: '1000px', margin: '0 auto', padding: '20px' }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '30px' }}>
        <h2 style={{ fontSize: '2.5rem', fontWeight: 800 }}>Dashboard</h2>
        <button onClick={() => { localStorage.removeItem('token'); window.location.href = '/login'; }} style={{ background: 'transparent', border: '1px solid var(--border)', padding: '8px 16px', borderRadius: '8px', color: 'var(--text-secondary)' }}>Logout</button>
      </div>
      
      <div className="card" style={{ marginBottom: '30px' }}>
        <h3 style={{ marginBottom: '20px', fontSize: '1.5rem' }}>Upload File</h3>
        <form onSubmit={handleUpload}>
          <div style={{ marginBottom: '20px', display: 'flex', gap: '20px', alignItems: 'center', flexWrap: 'wrap' }}>
            <input type="file" onChange={handleFileChange} className="input" style={{ flex: 1 }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <label style={{ whiteSpace: 'nowrap', color: 'var(--text-secondary)' }}>Expires in:</label>
              <select value={expiryDays} onChange={(e) => setExpiryDays(parseInt(e.target.value))} className="input" style={{ width: 'auto' }}>
                <option value={1}>1 Day</option>
                <option value={3}>3 Days</option>
                <option value={7}>7 Days</option>
              </select>
            </div>
          </div>
          {uploading && (
            <div className="progress-bar" style={{ marginBottom: '20px' }}>
              <div className="progress-fill" style={{ width: `${uploadProgress}%` }}></div>
            </div>
          )}
          <button type="submit" className="btn-primary" disabled={uploading} style={{ width: '100%' }}>
            {uploading ? 'Uploading...' : 'Upload File'}
          </button>
        </form>
      </div>

      <div className="card">
        <h3 style={{ marginBottom: '20px', fontSize: '1.5rem' }}>My Files</h3>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'separate', borderSpacing: '0' }}>
            <thead>
              <tr>
                <th style={{ padding: '15px', textAlign: 'left', borderBottom: '1px solid var(--border)' }}>Filename</th>
                <th style={{ padding: '15px', textAlign: 'left', borderBottom: '1px solid var(--border)' }}>Size</th>
                <th style={{ padding: '15px', textAlign: 'left', borderBottom: '1px solid var(--border)' }}>Expires</th>
                <th style={{ padding: '15px', textAlign: 'left', borderBottom: '1px solid var(--border)' }}>Downloads</th>
                <th style={{ padding: '15px', textAlign: 'left', borderBottom: '1px solid var(--border)' }}>Action</th>
              </tr>
            </thead>
            <tbody>
              {myFiles.map((file) => (
                <motion.tr 
                  key={file.id} 
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  whileHover={{ backgroundColor: 'rgba(255,255,255,0.02)' }}
                >
                  <td style={{ padding: '15px', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>{file.original_name}</td>
                  <td style={{ padding: '15px', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>{(file.size / 1024 / 1024).toFixed(2)} MB</td>
                  <td style={{ padding: '15px', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>{new Date(file.expires_at).toLocaleDateString()}</td>
                  <td style={{ padding: '15px', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>{file.download_count}</td>
                  <td style={{ padding: '15px', borderBottom: '1px solid rgba(255,255,255,0.05)' }}>
                    <button onClick={() => copyLink(file.unique_token)} className="btn-primary" style={{ fontSize: '0.8rem', padding: '6px 12px', background: 'rgba(255, 71, 87, 0.1)', color: '#ff4757', border: '1px solid rgba(255, 71, 87, 0.2)', boxShadow: 'none' }}>
                      Copy Link
                    </button>
                  </td>
                </motion.tr>
              ))}
              {myFiles.length === 0 && (
                <tr>
                  <td colSpan={5} style={{ padding: '30px', textAlign: 'center', color: 'var(--text-secondary)' }}>No files uploaded yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </motion.div>
  );
};

export default Dashboard;
