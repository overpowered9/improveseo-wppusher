import React from 'react';
import { useLocation } from 'react-router-dom';
import {
  Drawer,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Typography,
  Box,
  Divider,
  Button,
} from '@mui/material';
import {
  Dashboard as DashboardIcon,
  Description as DescriptionIcon,
  Layers as LayersIcon,
  AutoAwesome as AutoAwesomeIcon,
  Logout as LogoutIcon,
} from '@mui/icons-material';
import { useAuth } from '../../contexts/AuthContext';

const DRAWER_WIDTH = 280;

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: DashboardIcon },
  { name: 'Prompt Templates', href: '/templates', icon: DescriptionIcon },
  { name: 'Prompt Sets', href: '/sets', icon: LayersIcon },
  { name: 'Content Generator', href: '/generator', icon: AutoAwesomeIcon },
];

const Sidebar: React.FC = () => {
  const { logout } = useAuth();
  const location = useLocation();

  const handleNavigation = (href: string) => {
    window.location.href = href;
  };

  return (
    <Drawer
      variant="permanent"
      sx={{
        width: DRAWER_WIDTH,
        flexShrink: 0,
        '& .MuiDrawer-paper': {
          width: DRAWER_WIDTH,
          boxSizing: 'border-box',
          borderRight: '1px solid rgba(0, 0, 0, 0.12)',
          backgroundColor: 'background.paper',
        },
      }}
    >
      <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
        {/* Logo */}
        <Box sx={{ p: 3, display: 'flex', alignItems: 'center', gap: 1 }}>
          <AutoAwesomeIcon sx={{ color: 'primary.main', fontSize: 32 }} />
          <Typography variant="h6" component="div" sx={{ fontWeight: 600 }}>
            Prompt Admin
          </Typography>
        </Box>

        <Divider />

        {/* Navigation */}
        <Box sx={{ flexGrow: 1, py: 2 }}>
          <List>
            {navigation.map((item) => {
              const isActive = location.pathname === item.href;
              const IconComponent = item.icon;
              
              return (
                <ListItem key={item.name} disablePadding sx={{ px: 2, mb: 0.5 }}>
                  <ListItemButton
                    onClick={() => handleNavigation(item.href)}
                    sx={{
                      borderRadius: 2,
                      backgroundColor: isActive ? 'primary.main' : 'transparent',
                      color: isActive ? 'primary.contrastText' : 'text.primary',
                      '&:hover': {
                        backgroundColor: isActive ? 'primary.dark' : 'action.hover',
                      },
                    }}
                  >
                    <ListItemIcon
                      sx={{
                        color: isActive ? 'primary.contrastText' : 'text.secondary',
                        minWidth: 40,
                      }}
                    >
                      <IconComponent />
                    </ListItemIcon>
                    <ListItemText
                      primary={item.name}
                      primaryTypographyProps={{
                        fontWeight: isActive ? 600 : 500,
                        fontSize: '0.9rem',
                      }}
                    />
                  </ListItemButton>
                </ListItem>
              );
            })}
          </List>
        </Box>

        {/* User section */}
        <Box sx={{ p: 2 }}>
          <Divider sx={{ mb: 2 }} />
          <Button
            fullWidth
            startIcon={<LogoutIcon />}
            onClick={logout}
            variant="outlined"
            color="error"
            sx={{
              justifyContent: 'flex-start',
              textTransform: 'none',
              borderRadius: 2,
            }}
          >
            Logout
          </Button>
        </Box>
      </Box>
    </Drawer>
  );
};

export default Sidebar;