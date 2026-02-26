'use client';

import { useState, useEffect } from 'react';
import { Menu, X, FileText, Archive, Trash2, Clock, Star, LayoutGrid, Search } from 'lucide-react';
import Dashboard from '@/components/Dashboard';
import DocumentsList from '@/components/DocumentsList';
import Navigation from '@/components/Navigation';
import Header from '@/components/Header';
import BottomNav from '@/components/BottomNav';

export default function Home() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);

    // Register service worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch((err) => {
        console.log('[v0] Service Worker registration failed:', err);
      });
    }
  }, []);

  if (!mounted) return null;

  return (
    <div className="min-h-screen bg-background text-foreground flex flex-col">
      <Header mobileMenuOpen={mobileMenuOpen} setMobileMenuOpen={setMobileMenuOpen} />

      <div className="flex-1 flex overflow-hidden">
        {/* Desktop Sidebar */}
        <Navigation activeTab={activeTab} setActiveTab={setActiveTab} className="hidden md:flex" />

        {/* Mobile Menu Overlay */}
        {mobileMenuOpen && (
          <div className="fixed inset-0 z-40 md:hidden">
            <div
              className="absolute inset-0 bg-black/50"
              onClick={() => setMobileMenuOpen(false)}
            />
            <div className="absolute left-0 top-0 bottom-0 w-64 bg-card border-r border-border z-50">
              <Navigation activeTab={activeTab} setActiveTab={setActiveTab} onSelect={() => setMobileMenuOpen(false)} />
            </div>
          </div>
        )}

        {/* Main Content */}
        <main className="flex-1 overflow-y-auto pb-24 md:pb-0">
          {activeTab === 'dashboard' && <Dashboard />}
          {activeTab === 'recently' && <DocumentsList title="Recently" subtitle="Recently viewed documents" />}
          {activeTab === 'starred' && <DocumentsList title="Starred" subtitle="Your starred documents" />}
          {activeTab === 'documents' && <DocumentsList title="My Documents" subtitle="All your documents" />}
          {activeTab === 'archives' && <DocumentsList title="Archives" subtitle="Archived documents" status="archived" />}
          {activeTab === 'trash' && <DocumentsList title="Trash" subtitle="Deleted documents" status="deleted" />}
        </main>
      </div>

      {/* Mobile Bottom Navigation */}
      <BottomNav activeTab={activeTab} setActiveTab={setActiveTab} />
    </div>
  );
}
