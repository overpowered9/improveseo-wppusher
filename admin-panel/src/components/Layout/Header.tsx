import React from 'react';
import { useLocation } from 'react-router-dom';
import {
  AppBar,
  Toolbar,
  Typography,
  Avatar,
  Box,
  Chip,
} from '@mui/material';

const DRAWER_WIDTH = 280;

const Header: React.FC = () => {
  const location = useLocation();
  
  const getPageTitle = () => {
    switch (location.pathname) {
      case '/dashboard':
        return 'Dashboard';
      case '/templates':
        return 'Prompt Templates';
      case '/sets':
        return 'Prompt Sets';
      case '/generator':
        return 'Content Generator';
      default:
        return 'Prompt Admin';
    }
  };

  return (
    <AppBar
      position="fixed"
      sx={{
        width: `calc(100% - ${DRAWER_WIDTH}px)`,
        ml: `${DRAWER_WIDTH}px`,
        backgroundColor: 'background.paper',
        color: 'text.primary',
        boxShadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
        borderBottom: '1px solid rgba(0, 0, 0, 0.12)',
      }}
    >
      <Toolbar sx={{ justifyContent: 'space-between' }}>
        {/* Left side - Page Title */}
        <Typography variant="h5" component="h1" sx={{ fontWeight: 600 }}>
          {getPageTitle()}
        </Typography>

        {/* Right side - User info */}
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Chip
            label="Admin Panel"
            size="small"
            variant="outlined"
            sx={{ 
              fontWeight: 500,
              color: 'text.secondary',
              borderColor: 'divider',
            }}
          />
          
          <Avatar
            sx={{
              width: 32,
              height: 32,
              backgroundColor: 'primary.main',
              fontSize: '0.875rem',
              fontWeight: 600,
            }}
          >
            A
          </Avatar>
        </Box>
      </Toolbar>
    </AppBar>
  );
};

export default Header;