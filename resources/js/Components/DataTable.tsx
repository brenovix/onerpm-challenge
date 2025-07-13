import React, { useState, useEffect } from 'react';
import { AlertCircle, Loader2, RefreshCw } from 'lucide-react';

type Column = {
  key: string;
  label: string;
  sortable?: boolean;
  render?: (value: any, row: any) => React.ReactNode;
  className?: string;
}

type DataTableProps = {
  endpoint: string;
  title: string;
  columns: Column[] | string[];
  className?: string;
  onDataChange?: (data: any[]) => void;
  headers?: Record<string, string>;
  actions?: (row: any) => React.ReactNode;
};

type ApiResponse = {
  data: any[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from: number;
  to: number;
}

interface DataTableState {
  data: any[];
  loading: boolean;
  filteredData: any[];
  error: string | null;
  refreshing: boolean;
  sortColumn: string | null;
  sortDirection: 'asc' | 'desc';
}

const DataTable: React.FC<DataTableProps> = ({
  endpoint,
  title,
  columns,
  className = '',
  onDataChange,
  headers = {},
  actions,
}) => {
  const [state, setState] = useState<DataTableState>({
    data: [],
    loading: true,
    error: null,
    refreshing: false,
    sortColumn: null,
    sortDirection: 'asc',
    filteredData: [],
  });

  useEffect(() => {
    fetchData();
  }, [endpoint, state.sortColumn, state.sortDirection]);

  const fetchData = async () => {
    try {
      setState(prev => ({ ...prev, loading: true, error: null }));
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      const response = await fetch(endpoint, {
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken ?? '',
          'Accept': 'application/json',
          ...headers
        },
      });

      if (!response.ok) throw new Error('Retrieved data failed: ' + response.statusText);

      const result: ApiResponse = await response.json();

      setState(prev => ({
        ...prev,
        data: result.data,
        loading: false,
      }));
      onDataChange?.(result.data);
    } catch (error: any) {
      setState(prev => ({ ...prev, loading: false, error: error.message }));
    }
  };

  const handleSort = (columnKey: string) => {
    setState(prev => ({
      ...prev,
      sortColumn: columnKey,
      sortDirection:
        prev.sortColumn === columnKey && prev.sortDirection === 'asc'
          ? 'desc'
          : 'asc',
      filteredData: prev.data.sort((a, b) => {
        const aValue = a[columnKey];
        const bValue = b[columnKey];

        if (aValue < bValue) return prev.sortDirection === 'asc' ? -1 : 1;
        if (aValue > bValue) return prev.sortDirection === 'asc' ? 1 : -1;
        return 0;
      })
    }));
  };

  const handleRefresh = () => {
    setState(prev => ({ ...prev, refreshing: true }));
    fetchData().finally(() => setState(prev => ({ ...prev, refreshing: false })));
  };

  const getTableHeaders = (): Column[] => {
    if (!columns) {
      if (!state.data || state.data.length === 0) return [];
      return Object.keys(state.data[0]).map(key => ({ key, label: key }));
    }

    if (typeof columns[0] === 'string') {
      return (columns as string[]).map(col => ({ 
        key: col, 
        label: col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
        sortable: true
      }));
    }

    return columns as Column[];
  };

  const renderCell = (col: Column, row: any) => {
    const value = row[col.key];

    if (col.render) {
      return col.render(value, row);
    }

    if (!value || value.length === 0) {
      return <span className="text-gray-400 italic">-</span>;
    }

    if (typeof value === 'boolean') {
      return (
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
          value 
            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
        }`}>
          {value ? 'Active' : 'Inactive'}
        </span>
      );
    }

    if (typeof value === 'object') {
      return <span className="text-gray-500 italic">Object</span>;
    }
    
    return <span className="text-gray-900">{String(value)}</span>;
  };

  if (state.loading) {
    return (
      <div className={`table-container ${className}`}>
        <div className="flex items-center justify-center py-16">
          <div className="flex flex-col items-center space-y-4">
            <Loader2 className="h-8 w-8 animate-spin text-primary-600" />
            <div className="text-center">
              <p className="text-sm font-medium text-gray-900">Loading data...</p>
              <p className="text-xs text-gray-500">Please wait while data is being fetched.</p>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (state.error) {
    return (
      <div className={`table-container ${className}`}>
        <div className="p-6">
          <div className="rounded-md bg-red-50 border border-red-200 p-4">
            <div className="flex items-center">
              <AlertCircle className="h-5 w-5 text-red-400 mr-3" />
              <div className="flex-1">
                <h3 className="text-sm font-medium text-red-800">Error Loading Data</h3>
                <p className="mt-1 text-sm text-red-700">{state.error}</p>
              </div>
            </div>
            <div className="mt-4">
              <button
                onClick={() => fetchData()}
                className="btn-danger text-sm"
              >Try Again
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!state.data || state.data.length === 0) {
    return (
      <div className={`table-container ${className}`}>
        <div className="text-center py-16">
          <div className="mx-auto h-12 w-12 text-gray-400">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 className="mt-4 text-sm font-medium text-gray-900">No data available</h3>
          <p className="mt-2 text-sm text-gray-500">Get started by adding some data to your system.</p>
          <div className="mt-6">
            <button
              onClick={handleRefresh}
              className="btn-primary"
            >
              <RefreshCw className="h-4 w-4 mr-2" />
              Refresh Data
            </button>
          </div>
        </div>
      </div>
    );
  }

  const headings = getTableHeaders();

  return (
    <div className={`table-container ${className}`}>
      <div className='table-header'>
        <div className='flex intems-center space-x-4'>
          <h2 className='table-title'>{title}</h2>
          {
            state.filteredData.length !== state.data.length && (
              <span className='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>
                {state.filteredData.length} of {state.data.length} filtered
              </span>
            )
          }
          <button 
            onClick={handleRefresh}
            disabled={state.refreshing}
            className='btn-primary text-sm'
            >
              <RefreshCw className={`h-4 w-4 mr-2 ${state.refreshing ? 'animate-spin' : ''}`} />
              { state.refreshing ? 'Refreshing...' : 'Refresh' }
          </button>
        </div>
      </div>

      <div className='overflow-hidden'>
        <div className='overflow-x-auto'>
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                {headings.map(header => (
                  <th
                    key={header.key}
                    className={`table-header-cell ${header.sortable !== false ? 'cursor-pointer hover:bg-gray-100' : ''}`}
                    onClick={() => header.sortable !== false && handleSort(header.key)}
                  >
                    <div className="flex items-center space-x-1">
                      <span>{header.label || header.key}</span>
                      {header.sortable && state.sortColumn === header.key && (
                        <span className="text-primary-600">
                          {state.sortDirection === 'asc' ? '↑' : '↓'}
                        </span>
                      )}
                    </div>
                  </th>
                ))}
                {actions && (
                  <th className="table-header-cell">Actions</th>
                )}
              </tr>
            </thead>
            <tbody className='bg-white divide-y divide-gray-200'>
              {state.filteredData.map((row: any, index: number) => (
                <tr key={row.id || index} className="hover:bg-gray-50 transition-colors">
                  {headings.map(col => (
                    <td key={col.key} className={`table-cell ${col.className || ''}`}>
                      {renderCell(col, row)}
                    </td>
                  ))}
                  {actions && (
                    <td className="table-cell">
                      <div className='flex items-center space-x-2'>
                        {actions(row)}
                      </div>
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <div className="bg-gray-50 px-6 py-3 border-t border-gray-200">
        <div className="flex items-center justify-between">
          <p className="text-sm text-gray-700">
            Showing <span className="font-medium">{state.filteredData.length}</span> of{' '}
            <span className="font-medium">{state.data.length}</span> results
          </p>
        </div>
      </div>
    </div>
  );
};

export default DataTable;