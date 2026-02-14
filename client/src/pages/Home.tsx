import React from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';

const Home = () => {
  const container = {
    hidden: { opacity: 0 },
    show: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1
      }
    }
  };

  const item = {
    hidden: { opacity: 0, y: 30 },
    show: { opacity: 1, y: 0, transition: { type: "spring", stiffness: 50 } }
  };

  return (
    <div style={{ maxWidth: '1000px', margin: '0 auto', padding: '60px 20px', textAlign: 'center' }}>
      <motion.div 
        initial="hidden"
        animate="show"
        variants={container}
      >
        <motion.h1 variants={item} style={{ fontSize: '3.5rem', marginBottom: '1rem', fontWeight: 800, letterSpacing: '-1px' }}>
          file.nesneek<span style={{ color: 'var(--accent)' }}>.com</span>
        </motion.h1>
        <motion.p variants={item} style={{ color: 'var(--text-secondary)', marginBottom: '4rem', fontSize: '1.2rem', maxWidth: '600px', margin: '0 auto 4rem' }}>
          Secure, encrypted, and temporary file sharing for professionals.
        </motion.p>
        
        <motion.div 
          variants={container}
          style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '30px', marginBottom: '60px' }}
        >
          {[
            { title: "Security", desc: "End-to-end like protection with temporary links.", color: "#ff4757" },
            { title: "Privacy", desc: "Files stored individually in isolated containers.", color: "#2ed573" },
            { title: "Access Control", desc: "Invite-only system ensures trusted access.", color: "#ffa502" },
            { title: "Auto Deletion", desc: "Files vanish after download or expiration.", color: "#5352ed" }
          ].map((feature, i) => (
            <motion.div 
              key={i}
              variants={item}
              whileHover={{ y: -10, transition: { duration: 0.2 } }}
              className="card"
              style={{ textAlign: 'left', position: 'relative', overflow: 'hidden' }}
            >
              <div style={{ 
                position: 'absolute', top: 0, left: 0, width: '100%', height: '4px', 
                background: `linear-gradient(90deg, ${feature.color}, transparent)` 
              }} />
              <h3 style={{ color: 'var(--text-primary)', marginBottom: '12px', fontSize: '1.25rem' }}>{feature.title}</h3>
              <p style={{ color: 'var(--text-secondary)', lineHeight: 1.6 }}>{feature.desc}</p>
            </motion.div>
          ))}
        </motion.div>
        
        <motion.div variants={item} style={{ display: 'flex', justifyContent: 'center', gap: '20px' }}>
          <Link to="/login" className="btn-primary" style={{ textDecoration: 'none', padding: '15px 40px', fontSize: '1.1rem' }}>Get Started</Link>
          <Link to="/register" className="btn-primary" style={{ textDecoration: 'none', background: 'transparent', border: '1px solid var(--border)', boxShadow: 'none' }}>Register</Link>
        </motion.div>
      </motion.div>
    </div>
  );
};

export default Home;
