import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import api from '../api';

const AdminDashboard = () => {
  const [activeTab, setActiveTab] = useState('users');
  const [users, setUsers] = useState<any[]>([]);
  const [files, setFiles] = useState<any[]>([]);
  const [stats, setStats] = useState<any>({});
  const [inviteCount, setInviteCount] = useState(10);

  useEffect(() => {
    fetchStats();
    if (activeTab === 'users') fetchUsers();
    if (activeTab === 'files') fetchFiles();
  }, [activeTab]);

  const fetchStats = async () => {
    try {
      const res = await api.get('/admin/stats');
      setStats(res.data);
    } catch (err) { console.error(err); }
  };

  const fetchUsers = async () => {
    try {
      const res = await api.get('/admin/users');
      setUsers(res.data);
    } catch (err) { console.error(err); }
  };

  const fetchFiles = async () => {
    try {
      const res = await api.get('/admin/files');
      setFiles(res.data);
    } catch (err) { console.error(err); }
  };

  const toggleBlock = async (id: number) => {
    try {
      await api.put(`/admin/users/${id}/block`);
      fetchUsers();
    } catch (err) { console.error(err); }
  };

  const generateInvites = async () => {
    try {
      const res = await api.post('/admin/invites', { count: inviteCount });
      alert(`${res.data.invites.length} invites generated! Codes:\n` + res.data.invites.map((i: any) => i.code).join('\n'));
      fetchStats();
    } catch (err) { console.error(err); }
  };

  const deleteFile = async (id: number) => {
    if (!window.confirm('Are you sure?')) return;
    try {
      await api.delete(`/admin/files/${id}`);
      fetchFiles();
      fetchStats();
    } catch (err) { console.error(err); }
  };

  const container = {
    hidden: { opacity: 0 },
    show: {
      opacity: 1,
      transition: { staggerChildren: 0.1 }
    }
  };

  const item = {
    hidden: { opacity: 0, y: 20 },
    show: { opacity: 1, y: 0 }
  };

  return (
    <motion.div 
      initial="hidden"
      animate="show"
      variants={container}
      style={{ maxWidth: '1200px', margin: '0 auto', padding: '40px 20px' }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '30px' }}>
        <h2 style={{ fontSize: '2.5rem', fontWeight: 800 }}>Admin Dashboard</h2>
        <button onClick={() => { localStorage.removeItem('token'); window.location.href = '/login'; }} style={{ background: 'transparent', border: '1px solid var(--border)', padding: '8px 16px', borderRadius: '8px', color: 'var(--text-secondary)' }}>Logout</button>
      </div>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '20px', marginBottom: '40px' }}>
        {[
            { title: "Users", value: stats.users || 0, color: "#ff4757" },
            { title: "Files", value: stats.files || 0, color: "#2ed573" },
            { title: "Used Invites", value: stats.used_invites || 0, color: "#ffa502" }
        ].map((stat, i) => (
            <motion.div variants={item} key={i} className="card" style={{ textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '4px', background: stat.color }} />
                <h3 style={{ fontSize: '1.2rem', color: 'var(--text-secondary)', marginBottom: '10px' }}>{stat.title}</h3>
                <p style={{ fontSize: '3rem', fontWeight: 800, margin: 0 }}>{stat.value}</p>
            </motion.div>
        ))}
      </div>

      <div style={{ marginBottom: '30px', display: 'flex', gap: '10px', background: 'rgba(255,255,255,0.05)', padding: '5px', borderRadius: '12px', width: 'fit-content' }}>
        {['users', 'files', 'invites'].map((tab) => (
            <button 
                key={tab}
                onClick={() => setActiveTab(tab)}
                style={{ 
                    padding: '10px 20px', 
                    borderRadius: '8px', 
                    border: 'none',
                    background: activeTab === tab ? 'rgba(255,255,255,0.1)' : 'transparent',
                    color: activeTab === tab ? 'white' : 'var(--text-secondary)',
                    fontWeight: activeTab === tab ? 600 : 400,
                    textTransform: 'capitalize'
                }}
            >
                {tab}
            </button>
        ))}
      </div>

      <motion.div 
        key={activeTab}
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
      >
        {activeTab === 'users' && (
            <div className="card" style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'separate', borderSpacing: '0' }}>
                <thead>
                <tr>
                    <th style={{ padding: '15px' }}>ID</th>
                    <th style={{ padding: '15px' }}>Username</th>
                    <th style={{ padding: '15px' }}>Role</th>
                    <th style={{ padding: '15px' }}>Status</th>
                    <th style={{ padding: '15px' }}>Action</th>
                </tr>
                </thead>
                <tbody>
                {users.map(u => (
                    <tr key={u.id}>
                    <td style={{ padding: '15px' }}>{u.id}</td>
                    <td style={{ padding: '15px', fontWeight: 600 }}>{u.username}</td>
                    <td style={{ padding: '15px' }}>
                        <span style={{ padding: '4px 8px', borderRadius: '4px', background: u.role === 'admin' ? 'rgba(255, 71, 87, 0.1)' : 'rgba(255,255,255,0.1)', color: u.role === 'admin' ? '#ff4757' : 'inherit', fontSize: '0.8rem' }}>{u.role}</span>
                    </td>
                    <td style={{ padding: '15px' }}>
                        <span style={{ color: u.is_blocked ? '#ff4757' : '#2ed573' }}>{u.is_blocked ? 'Blocked' : 'Active'}</span>
                    </td>
                    <td style={{ padding: '15px' }}>
                        {u.role !== 'admin' && (
                            <button onClick={() => toggleBlock(u.id)} className="btn-primary" style={{ backgroundColor: 'transparent', backgroundImage: 'none', border: `1px solid ${u.is_blocked ? '#2ed573' : '#ff4757'}`, color: u.is_blocked ? '#2ed573' : '#ff4757', fontSize: '0.8rem', padding: '6px 12px', boxShadow: 'none' }}>
                            {u.is_blocked ? 'Unblock' : 'Block'}
                            </button>
                        )}
                    </td>
                    </tr>
                ))}
                </tbody>
            </table>
            </div>
        )}

        {activeTab === 'files' && (
            <div className="card" style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'separate', borderSpacing: '0' }}>
                <thead>
                <tr>
                    <th style={{ padding: '15px' }}>ID</th>
                    <th style={{ padding: '15px' }}>Filename</th>
                    <th style={{ padding: '15px' }}>User</th>
                    <th style={{ padding: '15px' }}>Size</th>
                    <th style={{ padding: '15px' }}>Deleted</th>
                    <th style={{ padding: '15px' }}>Action</th>
                </tr>
                </thead>
                <tbody>
                {files.map(f => (
                    <tr key={f.id}>
                    <td style={{ padding: '15px' }}>{f.id}</td>
                    <td style={{ padding: '15px', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{f.original_name}</td>
                    <td style={{ padding: '15px' }}>{f.user?.username}</td>
                    <td style={{ padding: '15px' }}>{(f.size / 1024 / 1024).toFixed(2)} MB</td>
                    <td style={{ padding: '15px' }}>
                        <span style={{ color: f.is_deleted ? '#ff4757' : '#2ed573' }}>{f.is_deleted ? 'Yes' : 'No'}</span>
                    </td>
                    <td style={{ padding: '15px' }}>
                        <button onClick={() => deleteFile(f.id)} className="btn-primary" style={{ backgroundColor: 'transparent', backgroundImage: 'none', border: '1px solid #ff4757', color: '#ff4757', fontSize: '0.8rem', padding: '6px 12px', boxShadow: 'none' }}>
                        Delete
                        </button>
                    </td>
                    </tr>
                ))}
                </tbody>
            </table>
            </div>
        )}

        {activeTab === 'invites' && (
            <div className="card" style={{ maxWidth: '500px' }}>
            <h3 style={{ marginBottom: '20px' }}>Generate Invites</h3>
            <div style={{ display: 'flex', gap: '20px', alignItems: 'center' }}>
                <input 
                    type="number" 
                    className="input" 
                    value={inviteCount} 
                    onChange={(e) => setInviteCount(parseInt(e.target.value))} 
                    style={{ width: '100px' }}
                />
                <button onClick={generateInvites} className="btn-primary">Generate</button>
            </div>
            </div>
        )}
      </motion.div>
    </motion.div>
  );
};

export default AdminDashboard;
