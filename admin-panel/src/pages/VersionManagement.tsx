import React, { useState, useEffect } from 'react';
import {
  Box,
  Card,
  Typography,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Chip,
  IconButton,
  Menu,
  MenuItem,
  ListItemIcon,
  ListItemText,
  Alert,
  Snackbar,
  CircularProgress,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Tooltip,
  Stack,
  Avatar,
} from '@mui/material';
import {
  Add as AddIcon,
  PlayArrow as ActivateIcon,
  Delete as DeleteIcon,
  MoreVert as MoreVertIcon,
  CheckCircle as ActiveIcon,
  RadioButtonUnchecked as InactiveIcon,
  Visibility as ViewIcon,
} from '@mui/icons-material';
import { useParams, useNavigate } from 'react-router-dom';
import apiClient from '../services/api.js';
import type { PromptTemplate, PromptVersion } from '../services/api.js';

interface VersionDialogProps {
  open: boolean;
  onClose: () => void;
  templateId: string;
  onSave: () => void;
}

const CreateVersionDialog: React.FC<VersionDialogProps> = ({ open, onClose, templateId, onSave }) => {
  const [formData, setFormData] = useState({
    prompt: '',
    notes: '',
    expectedOutput: '',
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.prompt.trim()) newErrors.prompt = 'Prompt content is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;
    
    setLoading(true);
    try {
      await apiClient.createTemplateVersion(templateId, formData);
      onSave();
      onClose();
      setFormData({ prompt: '', notes: '', expectedOutput: '' });
    } catch (error) {
      console.error('Failed to create version:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>Create New Version</DialogTitle>
      <DialogContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, mt: 1 }}>
          <TextField
            label="Prompt Content"
            value={formData.prompt}
            onChange={(e) => setFormData(prev => ({ ...prev, prompt: e.target.value }))}
            error={!!errors.prompt}
            helperText={errors.prompt || 'Use {{variable_name}} for template variables'}
            multiline
            rows={8}
            fullWidth
            placeholder="Enter your prompt template here..."
          />

          <TextField
            label="Version Notes (Optional)"
            value={formData.notes}
            onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
            multiline
            rows={2}
            fullWidth
            placeholder="Describe what changed in this version..."
          />

          <TextField
            label="Expected Output (Optional)"
            value={formData.expectedOutput}
            onChange={(e) => setFormData(prev => ({ ...prev, expectedOutput: e.target.value }))}
            multiline
            rows={3}
            fullWidth
            placeholder="Describe what kind of output this prompt should generate..."
          />
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Cancel</Button>
        <Button 
          onClick={handleSubmit} 
          variant="contained" 
          disabled={loading}
          startIcon={loading && <CircularProgress size={20} />}
        >
          Create Version
        </Button>
      </DialogActions>
    </Dialog>
  );
};

interface ViewVersionDialogProps {
  open: boolean;
  onClose: () => void;
  version: PromptVersion | null;
}

const ViewVersionDialog: React.FC<ViewVersionDialogProps> = ({ open, onClose, version }) => {
  if (!version) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Version {version.version} Details
        {version.isActive && (
          <Chip 
            label="Active" 
            color="success" 
            size="small" 
            sx={{ ml: 2 }}
            icon={<ActiveIcon />}
          />
        )}
      </DialogTitle>
      <DialogContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, mt: 1 }}>
          <Box>
            <Typography variant="subtitle2" gutterBottom>Prompt Content:</Typography>
            <Paper sx={{ p: 2, bgcolor: 'grey.50' }}>
              <Typography variant="body2" component="pre" sx={{ whiteSpace: 'pre-wrap' }}>
                {version.prompt}
              </Typography>
            </Paper>
          </Box>

          {version.metadata?.notes && (
            <Box>
              <Typography variant="subtitle2" gutterBottom>Notes:</Typography>
              <Typography variant="body2">{version.metadata.notes}</Typography>
            </Box>
          )}

          {version.metadata?.expectedOutput && (
            <Box>
              <Typography variant="subtitle2" gutterBottom>Expected Output:</Typography>
              <Typography variant="body2">{version.metadata.expectedOutput}</Typography>
            </Box>
          )}

          <Box>
            <Typography variant="subtitle2" gutterBottom>Metadata:</Typography>
            <Typography variant="body2" color="text.secondary">
              Created: {new Date(version.createdAt).toLocaleString()}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Creator: {version.createdBy}
            </Typography>
          </Box>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Close</Button>
      </DialogActions>
    </Dialog>
  );
};

const VersionManagement: React.FC = () => {
  const { templateId } = useParams<{ templateId: string }>();
  const navigate = useNavigate();
  const [template, setTemplate] = useState<PromptTemplate | null>(null);
  const [versions, setVersions] = useState<PromptVersion[]>([]);
  const [loading, setLoading] = useState(true);
  const [createDialogOpen, setCreateDialogOpen] = useState(false);
  const [viewDialogOpen, setViewDialogOpen] = useState(false);
  const [selectedVersion, setSelectedVersion] = useState<PromptVersion | null>(null);
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const [menuVersion, setMenuVersion] = useState<PromptVersion | null>(null);
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false,
    message: '',
    severity: 'success'
  });

  const fetchData = async () => {
    if (!templateId) return;
    
    try {
      setLoading(true);
      const [templateResponse, versionsResponse] = await Promise.all([
        apiClient.getPromptTemplates(), // We'll filter by ID
        apiClient.getTemplateVersions(templateId)
      ]);

      if (templateResponse.success && versionsResponse.success) {
        const foundTemplate = templateResponse.data?.find(t => t._id === templateId);
        setTemplate(foundTemplate || null);
        setVersions(versionsResponse.data!);
      }
    } catch (error) {
      console.error('Failed to fetch data:', error);
      showSnackbar('Failed to fetch template data', 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [templateId]);

  const showSnackbar = (message: string, severity: 'success' | 'error') => {
    setSnackbar({ open: true, message, severity });
  };

  const handleActivateVersion = async (versionId: string) => {
    if (!templateId) return;

    try {
      const response = await apiClient.activateTemplateVersion(templateId, versionId);
      if (response.success) {
        fetchData(); // Refresh data
        showSnackbar('Version activated successfully', 'success');
      }
    } catch (error) {
      console.error('Failed to activate version:', error);
      showSnackbar('Failed to activate version', 'error');
    }
    handleMenuClose();
  };

  const handleDeleteVersion = async (versionId: string) => {
    if (!templateId) return;

    if (window.confirm('Are you sure you want to delete this version?')) {
      try {
        const response = await apiClient.deleteTemplateVersion(templateId, versionId);
        if (response.success) {
          fetchData(); // Refresh data
          showSnackbar('Version deleted successfully', 'success');
        }
      } catch (error) {
        console.error('Failed to delete version:', error);
        showSnackbar('Failed to delete version', 'error');
      }
    }
    handleMenuClose();
  };

  const handleMenuOpen = (event: React.MouseEvent<HTMLElement>, version: PromptVersion) => {
    setAnchorEl(event.currentTarget);
    setMenuVersion(version);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setMenuVersion(null);
  };

  const openViewDialog = (version: PromptVersion) => {
    setSelectedVersion(version);
    setViewDialogOpen(true);
    handleMenuClose();
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  if (!template) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="error">Template not found</Alert>
      </Box>
    );
  }

  const activeVersion = versions.find(v => v.isActive);

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ mb: 3 }}>
        <Button 
          onClick={() => navigate('/prompt-templates')} 
          sx={{ mb: 2 }}
        >
          ← Back to Templates
        </Button>
        
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
          <Box>
            <Typography variant="h4" component="h1" gutterBottom>
              {template.name} - Versions
            </Typography>
            <Typography variant="body1" color="text.secondary" gutterBottom>
              {template.description}
            </Typography>
            <Stack direction="row" spacing={1} sx={{ mt: 1 }}>
              <Chip label={template.category} size="small" variant="outlined" />
              <Chip label={template.wordCountVariant} size="small" />
              {template.sectionType && (
                <Chip label={template.sectionType} size="small" variant="outlined" />
              )}
            </Stack>
          </Box>
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => setCreateDialogOpen(true)}
            size="large"
          >
            Create Version
          </Button>
        </Box>
      </Box>

      {/* Active Version Info */}
      {activeVersion && (
        <Card sx={{ mb: 3, border: '2px solid', borderColor: 'success.main' }}>
          <Box sx={{ p: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              <ActiveIcon color="success" sx={{ mr: 1 }} />
              <Typography variant="h6">
                Active Version {activeVersion.version}
              </Typography>
            </Box>
            <Typography variant="body2" color="text.secondary" paragraph>
              Created: {new Date(activeVersion.createdAt).toLocaleString()}
            </Typography>
            <Typography variant="body2" sx={{ mb: 2 }}>
              {activeVersion.metadata?.notes || 'No notes provided'}
            </Typography>
            <Button
              variant="outlined"
              size="small"
              onClick={() => openViewDialog(activeVersion)}
              startIcon={<ViewIcon />}
            >
              View Details
            </Button>
          </Box>
        </Card>
      )}

      {/* Versions Table */}
      <Card>
        <Box sx={{ p: 2, borderBottom: 1, borderColor: 'divider' }}>
          <Typography variant="h6">
            All Versions ({versions.length})
          </Typography>
        </Box>
        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Version</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Notes</TableCell>
                <TableCell>Created</TableCell>
                <TableCell>Creator</TableCell>
                <TableCell align="center">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {versions.map((version) => (
                <TableRow key={version._id} hover>
                  <TableCell>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      <Avatar sx={{ width: 32, height: 32, mr: 2, fontSize: 14 }}>
                        v{version.version}
                      </Avatar>
                      <Typography variant="subtitle2">
                        Version {version.version}
                      </Typography>
                    </Box>
                  </TableCell>
                  <TableCell>
                    {version.isActive ? (
                      <Chip 
                        label="Active" 
                        color="success" 
                        size="small"
                        icon={<ActiveIcon />}
                      />
                    ) : (
                      <Chip 
                        label="Inactive" 
                        variant="outlined" 
                        size="small"
                        icon={<InactiveIcon />}
                      />
                    )}
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2">
                      {version.metadata?.notes || 'No notes'}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2" color="text.secondary">
                      {new Date(version.createdAt).toLocaleDateString()}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2" color="text.secondary">
                      {version.createdBy}
                    </Typography>
                  </TableCell>
                  <TableCell align="center">
                    <Tooltip title="More actions">
                      <IconButton
                        size="small"
                        onClick={(e) => handleMenuOpen(e, version)}
                      >
                        <MoreVertIcon />
                      </IconButton>
                    </Tooltip>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </Card>

      {/* Action Menu */}
      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={handleMenuClose}
      >
        <MenuItem onClick={() => menuVersion && openViewDialog(menuVersion)}>
          <ListItemIcon>
            <ViewIcon fontSize="small" />
          </ListItemIcon>
          <ListItemText>View Details</ListItemText>
        </MenuItem>
        
        {menuVersion && !menuVersion.isActive && (
          <MenuItem onClick={() => menuVersion && handleActivateVersion(menuVersion._id)}>
            <ListItemIcon>
              <ActivateIcon fontSize="small" />
            </ListItemIcon>
            <ListItemText>Activate</ListItemText>
          </MenuItem>
        )}
        
        {menuVersion && !menuVersion.isActive && (
          <MenuItem 
            onClick={() => menuVersion && handleDeleteVersion(menuVersion._id)}
            sx={{ color: 'error.main' }}
          >
            <ListItemIcon>
              <DeleteIcon fontSize="small" color="error" />
            </ListItemIcon>
            <ListItemText>Delete</ListItemText>
          </MenuItem>
        )}
      </Menu>

      {/* Dialogs */}
      <CreateVersionDialog
        open={createDialogOpen}
        onClose={() => setCreateDialogOpen(false)}
        templateId={templateId!}
        onSave={fetchData}
      />

      <ViewVersionDialog
        open={viewDialogOpen}
        onClose={() => setViewDialogOpen(false)}
        version={selectedVersion}
      />

      {/* Snackbar */}
      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={() => setSnackbar(prev => ({ ...prev, open: false }))}
      >
        <Alert severity={snackbar.severity} onClose={() => setSnackbar(prev => ({ ...prev, open: false }))}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};

export default VersionManagement;