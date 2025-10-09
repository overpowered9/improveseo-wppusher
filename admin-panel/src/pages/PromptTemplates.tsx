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
  MenuItem,
  FormControl,
  InputLabel,
  Select,
  Chip,
  IconButton,
  Menu,
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
  TablePagination,
} from '@mui/material';
import {
  Add as AddIcon,
  Edit as EditIcon,
  MoreVert as MoreVertIcon,
  History as HistoryIcon,
  ContentCopy as CopyIcon,
} from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import apiClient from '../services/api.js';
import type { PromptTemplate } from '../services/api.js';

interface PromptTemplateFormData {
  name: string;
  description: string;
  category: 'content_generation' | 'meta_generation' | 'format_template';
  wordCountVariant: 'small' | 'medium' | 'large' | 'all';
  sectionType?: 'introduction' | 'section' | 'conclusion' | 'faqs' | 'whats_next' | 'meta_title' | 'meta_description';
  variables: string[];
}

interface TemplateDialogProps {
  open: boolean;
  onClose: () => void;
  template?: PromptTemplate;
  onSave: (data: PromptTemplateFormData) => Promise<void>;
}

const TemplateDialog: React.FC<TemplateDialogProps> = ({ open, onClose, template, onSave }) => {
  const [formData, setFormData] = useState<PromptTemplateFormData>({
    name: '',
    description: '',
    category: 'content_generation',
    wordCountVariant: 'medium',
    variables: [],
  });
  const [newVariable, setNewVariable] = useState('');
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const categories = [
    { value: 'content_generation', label: 'Content Generation' },
    { value: 'meta_generation', label: 'Meta Generation' },
    { value: 'format_template', label: 'Format Template' },
  ] as const;

  const wordCountVariants = [
    { value: 'small', label: 'Small' },
    { value: 'medium', label: 'Medium' },
    { value: 'large', label: 'Large' },
    { value: 'all', label: 'All' },
  ] as const;

  const sectionTypes = [
    { value: 'introduction', label: 'Introduction' },
    { value: 'section', label: 'Section' },
    { value: 'conclusion', label: 'Conclusion' },
    { value: 'faqs', label: 'FAQs' },
    { value: 'whats_next', label: "What's Next" },
    { value: 'meta_title', label: 'Meta Title' },
    { value: 'meta_description', label: 'Meta Description' },
  ] as const;

  useEffect(() => {
    if (template) {
      setFormData({
        name: template.name,
        description: template.description,
        category: template.category,
        wordCountVariant: template.wordCountVariant,
        sectionType: template.sectionType,
        variables: template.variables || [],
      });
    } else {
      setFormData({
        name: '',
        description: '',
        category: 'content_generation',
        wordCountVariant: 'medium',
        variables: [],
      });
    }
    setErrors({});
  }, [template, open]);

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};
    
    if (!formData.name.trim()) newErrors.name = 'Name is required';
    if (!formData.description.trim()) newErrors.description = 'Description is required';
    if (!formData.category) newErrors.category = 'Category is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;
    
    setLoading(true);
    try {
      await onSave(formData);
      onClose();
    } catch (error) {
      console.error('Failed to save template:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAddVariable = () => {
    if (newVariable.trim() && !formData.variables.includes(newVariable.trim())) {
      setFormData(prev => ({ ...prev, variables: [...prev.variables, newVariable.trim()] }));
      setNewVariable('');
    }
  };

  const handleRemoveVariable = (variableToRemove: string) => {
    setFormData(prev => ({ 
      ...prev, 
      variables: prev.variables.filter(variable => variable !== variableToRemove) 
    }));
  };

  const handleKeyPress = (event: React.KeyboardEvent) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      handleAddVariable();
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {template ? 'Edit Template' : 'Create New Template'}
      </DialogTitle>
      <DialogContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, mt: 1 }}>
          <Box sx={{ display: 'flex', gap: 2 }}>
            <TextField
              label="Template Name"
              value={formData.name}
              onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
              error={!!errors.name}
              helperText={errors.name}
              fullWidth
            />
            <FormControl fullWidth error={!!errors.category}>
              <InputLabel>Category</InputLabel>
              <Select
                value={formData.category}
                label="Category"
                onChange={(e) => setFormData(prev => ({ ...prev, category: e.target.value as PromptTemplateFormData['category'] }))}
              >
                {categories.map((category) => (
                  <MenuItem key={category.value} value={category.value}>
                    {category.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>

          <TextField
            label="Description"
            value={formData.description}
            onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
            error={!!errors.description}
            helperText={errors.description}
            multiline
            rows={2}
            fullWidth
          />

          <Box sx={{ display: 'flex', gap: 2 }}>
            <FormControl fullWidth>
              <InputLabel>Word Count Variant</InputLabel>
              <Select
                value={formData.wordCountVariant}
                label="Word Count Variant"
                onChange={(e) => setFormData(prev => ({ 
                  ...prev, 
                  wordCountVariant: e.target.value as PromptTemplateFormData['wordCountVariant'] 
                }))}
              >
                {wordCountVariants.map((variant) => (
                  <MenuItem key={variant.value} value={variant.value}>
                    {variant.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>

            <FormControl fullWidth>
              <InputLabel>Section Type (Optional)</InputLabel>
              <Select
                value={formData.sectionType || ''}
                label="Section Type (Optional)"
                onChange={(e) => setFormData(prev => ({ 
                  ...prev, 
                  sectionType: e.target.value as PromptTemplateFormData['sectionType'] || undefined
                }))}
              >
                <MenuItem value="">None</MenuItem>
                {sectionTypes.map((type) => (
                  <MenuItem key={type.value} value={type.value}>
                    {type.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>

          {/* Variables Section */}
          <Box>
            <Typography variant="subtitle2" sx={{ mb: 1 }}>
              Variables
            </Typography>
            <Box sx={{ display: 'flex', gap: 1, mb: 2, flexWrap: 'wrap' }}>
              {formData.variables.map((variable) => (
                <Chip
                  key={variable}
                  label={variable}
                  onDelete={() => handleRemoveVariable(variable)}
                  size="small"
                />
              ))}
            </Box>
            <Box sx={{ display: 'flex', gap: 1 }}>
              <TextField
                size="small"
                placeholder="Add variable"
                value={newVariable}
                onChange={(e) => setNewVariable(e.target.value)}
                onKeyPress={handleKeyPress}
              />
              <Button onClick={handleAddVariable} variant="outlined" size="small">
                Add Variable
              </Button>
            </Box>
          </Box>
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
          {template ? 'Update' : 'Create'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};

const PromptTemplates: React.FC = () => {
  const navigate = useNavigate();
  const [templates, setTemplates] = useState<PromptTemplate[]>([]);
  const [loading, setLoading] = useState(true);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [selectedTemplate, setSelectedTemplate] = useState<PromptTemplate | undefined>();
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const [menuTemplate, setMenuTemplate] = useState<PromptTemplate | null>(null);
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false,
    message: '',
    severity: 'success'
  });
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);

  const fetchTemplates = async () => {
    try {
      const response = await apiClient.getPromptTemplates();
      if (response.success) {
        setTemplates(response.data!);
      }
    } catch (error) {
      console.error('Failed to fetch templates:', error);
      showSnackbar('Failed to fetch templates', 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTemplates();
  }, []);

  const showSnackbar = (message: string, severity: 'success' | 'error') => {
    setSnackbar({ open: true, message, severity });
  };

  const handleCreateTemplate = async (data: PromptTemplateFormData) => {
    const response = await apiClient.createPromptTemplate({
      name: data.name,
      description: data.description,
      category: data.category,
      wordCountVariant: data.wordCountVariant,
      sectionType: data.sectionType,
      variables: data.variables,
    });

    if (response.success) {
      fetchTemplates();
      showSnackbar('Template created successfully', 'success');
    } else {
      throw new Error('Failed to create template');
    }
  };

  const handleUpdateTemplate = async (data: PromptTemplateFormData) => {
    if (!selectedTemplate) return;

    const response = await apiClient.updatePromptTemplate(selectedTemplate._id, {
      description: data.description,
      variables: data.variables,
    });

    if (response.success) {
      fetchTemplates();
      showSnackbar('Template updated successfully', 'success');
    } else {
      throw new Error('Failed to update template');
    }
  };

  const handleDuplicateTemplate = async (template: PromptTemplate) => {
    const data: PromptTemplateFormData = {
      name: `${template.name} (Copy)`,
      description: template.description,
      category: template.category,
      wordCountVariant: template.wordCountVariant,
      sectionType: template.sectionType,
      variables: template.variables || [],
    };
    
    await handleCreateTemplate(data);
  };

  const handleMenuOpen = (event: React.MouseEvent<HTMLElement>, template: PromptTemplate) => {
    setAnchorEl(event.currentTarget);
    setMenuTemplate(template);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setMenuTemplate(null);
  };

  const openEditDialog = (template?: PromptTemplate) => {
    setSelectedTemplate(template);
    setDialogOpen(true);
    handleMenuClose();
  };

  const getCategoryLabel = (category: string) => {
    switch (category) {
      case 'content_generation': return 'Content Generation';
      case 'meta_generation': return 'Meta Generation';
      case 'format_template': return 'Format Template';
      default: return category;
    }
  };

  const getWordCountColor = (variant: string) => {
    switch (variant) {
      case 'small': return 'success';
      case 'medium': return 'warning';
      case 'large': return 'error';
      case 'all': return 'info';
      default: return 'default';
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Box>
          <Typography variant="h4" component="h1" gutterBottom>
            Prompt Templates
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Manage your AI prompt templates and versions
          </Typography>
        </Box>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => openEditDialog()}
          size="large"
        >
          Create Template
        </Button>
      </Box>

      {/* Templates Table */}
      <Card>
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Name</TableCell>
                <TableCell>Category</TableCell>
                <TableCell>Word Count</TableCell>
                <TableCell>Section Type</TableCell>
                <TableCell>Variables</TableCell>
                <TableCell>Updated</TableCell>
                <TableCell align="center">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {templates
                .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                .map((template) => (
                <TableRow key={template._id} hover>
                  <TableCell>
                    <Box>
                      <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                        {template.name}
                      </Typography>
                      <Typography variant="caption" color="text.secondary">
                        {template.description}
                      </Typography>
                    </Box>
                  </TableCell>
                  <TableCell>
                    <Chip label={getCategoryLabel(template.category)} size="small" variant="outlined" />
                  </TableCell>
                  <TableCell>
                    <Chip 
                      label={template.wordCountVariant} 
                      size="small" 
                      color={getWordCountColor(template.wordCountVariant) as any}
                    />
                  </TableCell>
                  <TableCell>
                    {template.sectionType ? (
                      <Chip label={template.sectionType} size="small" variant="outlined" />
                    ) : (
                      <Typography variant="body2" color="text.secondary">-</Typography>
                    )}
                  </TableCell>
                  <TableCell>
                    <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap' }}>
                      {template.variables.slice(0, 2).map((variable) => (
                        <Chip key={variable} label={variable} size="small" variant="outlined" />
                      ))}
                      {template.variables.length > 2 && (
                        <Chip label={`+${template.variables.length - 2}`} size="small" />
                      )}
                    </Box>
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2" color="text.secondary">
                      {new Date(template.updatedAt).toLocaleDateString()}
                    </Typography>
                  </TableCell>
                  <TableCell align="center">
                    <Tooltip title="More actions">
                      <IconButton
                        size="small"
                        onClick={(e) => handleMenuOpen(e, template)}
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
        
        <TablePagination
          rowsPerPageOptions={[5, 10, 25]}
          component="div"
          count={templates.length}
          rowsPerPage={rowsPerPage}
          page={page}
          onPageChange={(_, newPage) => setPage(newPage)}
          onRowsPerPageChange={(e) => {
            setRowsPerPage(parseInt(e.target.value, 10));
            setPage(0);
          }}
        />
      </Card>

      {/* Action Menu */}
      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={handleMenuClose}
      >
        <MenuItem onClick={() => openEditDialog(menuTemplate!)}>
          <ListItemIcon>
            <EditIcon fontSize="small" />
          </ListItemIcon>
          <ListItemText>Edit</ListItemText>
        </MenuItem>
        <MenuItem onClick={() => menuTemplate && handleDuplicateTemplate(menuTemplate)}>
          <ListItemIcon>
            <CopyIcon fontSize="small" />
          </ListItemIcon>
          <ListItemText>Duplicate</ListItemText>
        </MenuItem>
        <MenuItem onClick={() => menuTemplate && navigate(`/templates/${menuTemplate._id}/versions`)}>
          <ListItemIcon>
            <HistoryIcon fontSize="small" />
          </ListItemIcon>
          <ListItemText>View Versions</ListItemText>
        </MenuItem>
      </Menu>

      {/* Template Dialog */}
      <TemplateDialog
        open={dialogOpen}
        onClose={() => setDialogOpen(false)}
        template={selectedTemplate}
        onSave={selectedTemplate ? handleUpdateTemplate : handleCreateTemplate}
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

export default PromptTemplates;