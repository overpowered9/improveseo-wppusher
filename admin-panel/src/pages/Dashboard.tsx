import React, { useEffect, useState } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Avatar,
  CircularProgress,
  Chip,
  Button,
  Paper,
} from '@mui/material';
import {
  Description as DescriptionIcon,
  Layers as LayersIcon,
  AutoAwesome as AutoAwesomeIcon,
  CheckCircle as CheckCircleIcon,
  PlayArrow as PlayArrowIcon,
} from '@mui/icons-material';
import apiClient from '../services/api';
import type { CompletePromptSet } from '../services/api';

interface SystemStats {
  totalTemplates: number;
  totalPromptSets: number;
  hasActiveSet: boolean;
  activeSetName: string | null;
}

interface StatCardProps {
  title: string;
  value: string | number;
  icon: React.ReactNode;
  color?: 'primary' | 'success' | 'secondary';
}

const StatCard: React.FC<StatCardProps> = ({ title, value, icon, color = 'primary' }) => (
  <Card sx={{ height: '100%' }}>
    <CardContent>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <Avatar
          sx={{
            backgroundColor: `${color}.main`,
            color: `${color}.contrastText`,
            mr: 2,
          }}
        >
          {icon}
        </Avatar>
        <Box sx={{ flexGrow: 1 }}>
          <Typography color="text.secondary" gutterBottom variant="body2">
            {title}
          </Typography>
          <Typography variant="h5" component="div" sx={{ fontWeight: 600 }}>
            {value}
          </Typography>
        </Box>
      </Box>
    </CardContent>
  </Card>
);

const Dashboard: React.FC = () => {
  const [stats, setStats] = useState<SystemStats | null>(null);
  const [activeSet, setActiveSet] = useState<CompletePromptSet | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [healthResponse, activeSetResponse] = await Promise.all([
          apiClient.getSystemHealth(),
          apiClient.getActivePromptSet().catch(() => ({ success: false }))
        ]);

        if (healthResponse.success) {
          setStats(healthResponse.data!);
        }

        if (activeSetResponse.success) {
          setActiveSet((activeSetResponse as any).data);
        }
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return (
      <Box
        sx={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          height: 200,
        }}
      >
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box sx={{ flexGrow: 1, p: 3 }}>
      {/* Welcome section */}
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <Typography variant="h5" component="h2" gutterBottom sx={{ fontWeight: 600 }}>
            Welcome to Prompt Management System
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Manage your AI prompts, versions, and content generation with ease.
          </Typography>
        </CardContent>
      </Card>

      {/* Stats cards */}
      <Box sx={{ display: 'flex', gap: 3, mb: 3, flexWrap: 'wrap' }}>
        <Box sx={{ flex: '1 1 300px', minWidth: '300px' }}>
          <StatCard
            title="Prompt Templates"
            value={stats?.totalTemplates || 0}
            icon={<DescriptionIcon />}
            color="primary"
          />
        </Box>
        <Box sx={{ flex: '1 1 300px', minWidth: '300px' }}>
          <StatCard
            title="Prompt Sets"
            value={stats?.totalPromptSets || 0}
            icon={<LayersIcon />}
            color="primary"
          />
        </Box>
        <Box sx={{ flex: '1 1 300px', minWidth: '300px' }}>
          <StatCard
            title="Active Set"
            value={stats?.hasActiveSet ? 'Active' : 'None'}
            icon={stats?.hasActiveSet ? <CheckCircleIcon /> : <AutoAwesomeIcon />}
            color={stats?.hasActiveSet ? 'success' : 'secondary'}
          />
        </Box>
      </Box>

      {/* Active prompt set details */}
      {activeSet && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Typography variant="h6" gutterBottom sx={{ fontWeight: 600 }}>
              Active Prompt Set: {activeSet.name}
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              {activeSet.description}
            </Typography>
            
            <Box sx={{ 
              display: 'flex', 
              gap: 2, 
              flexWrap: 'wrap',
              '& > *': { flex: '1 1 300px', minWidth: '300px' }
            }}>
              {activeSet.prompts.map((prompt) => (
                <Paper key={prompt._id} sx={{ p: 2, backgroundColor: 'grey.50' }}>
                  <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                    {prompt.name}
                  </Typography>
                  <Typography variant="body2" color="text.secondary" sx={{ mt: 1, mb: 2 }}>
                    {prompt.description}
                  </Typography>
                  <Box sx={{ display: 'flex', gap: 1 }}>
                    <Chip
                      label={prompt.wordCountVariant}
                      size="small"
                      color="info"
                      variant="outlined"
                    />
                    <Chip
                      label={`v${prompt.activeVersion.version}`}
                      size="small"
                      color="success"
                      variant="outlined"
                    />
                  </Box>
                </Paper>
              ))}
            </Box>
          </CardContent>
        </Card>
      )}

      {/* Quick actions */}
      <Card>
        <CardContent>
          <Typography variant="h6" gutterBottom sx={{ fontWeight: 600 }}>
            Quick Actions
          </Typography>
          <Box sx={{ 
            display: 'flex', 
            gap: 3, 
            mt: 1, 
            flexWrap: 'wrap',
            '& > *': { flex: '1 1 300px', minWidth: '300px' }
          }}>
            <Button
              fullWidth
              variant="outlined"
              size="large"
              startIcon={<DescriptionIcon />}
              onClick={() => window.location.href = '/templates'}
              sx={{
                height: 100,
                flexDirection: 'column',
                gap: 1,
                textTransform: 'none',
              }}
            >
              <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                Manage Templates
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Create and edit prompt templates
              </Typography>
            </Button>
            
            <Button
              fullWidth
              variant="outlined"
              size="large"
              startIcon={<LayersIcon />}
              onClick={() => window.location.href = '/sets'}
              sx={{
                height: 100,
                flexDirection: 'column',
                gap: 1,
                textTransform: 'none',
              }}
            >
              <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                Prompt Sets
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Organize prompts into sets
              </Typography>
            </Button>
            
            <Button
              fullWidth
              variant="outlined"
              size="large"
              startIcon={<PlayArrowIcon />}
              onClick={() => window.location.href = '/generator'}
              sx={{
                height: 100,
                flexDirection: 'column',
                gap: 1,
                textTransform: 'none',
              }}
            >
              <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                Test Generator
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Test AI content generation
              </Typography>
            </Button>
          </Box>
        </CardContent>
      </Card>
    </Box>
  );
};

export default Dashboard;