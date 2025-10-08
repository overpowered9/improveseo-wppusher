import React, { createContext, useContext, useState, useEffect } from 'react';
import type { ReactNode } from 'react';
import apiClient from '../services/api';

interface AuthContextType {
  isAuthenticated: boolean;
  login: (username: string, password: string) => Promise<boolean>;
  logout: () => void;
  loading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    // Check if user is already logged in
    const token = localStorage.getItem('admin_token');
    if (token) {
      // For development: Accept any token
      setIsAuthenticated(true);
      setLoading(false);
      
      // Uncomment this for real API validation
      // apiClient.getSystemHealth()
      //   .then(() => {
      //     setIsAuthenticated(true);
      //   })
      //   .catch(() => {
      //     localStorage.removeItem('admin_token');
      //     setIsAuthenticated(false);
      //   })
      //   .finally(() => {
      //     setLoading(false);
      //   });
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (username: string, password: string): Promise<boolean> => {
    try {
      // For development: Mock authentication
      if (username === 'admin' && password === 'password') {
        localStorage.setItem('admin_token', 'mock-token-123');
        setIsAuthenticated(true);
        return true;
      }

      // Uncomment this for real API authentication
      // const response = await apiClient.login({ username, password });
      // if (response.success && response.data?.token) {
      //   localStorage.setItem('admin_token', response.data.token);
      //   setIsAuthenticated(true);
      //   return true;
      // }
      
      return false;
    } catch (error) {
      console.error('Login failed:', error);
      return false;
    }
  };

  const logout = () => {
    apiClient.logout();
    setIsAuthenticated(false);
  };

  const value = {
    isAuthenticated,
    login,
    logout,
    loading,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};