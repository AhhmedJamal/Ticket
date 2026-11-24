'use client';

import React, { useState, useMemo } from 'react';
import { NotionDatabase, DatabaseRow, DatabaseView } from '@/types';
import { databaseService } from '@/services/api';
import { useToast } from '@/lib/toast-context';
import {
  Table,
  Kanban,
  List,
  LayoutGrid,
  Plus,
  Search,
  X,
  Calendar,
  MoreHorizontal,
  Trash2,
} from 'lucide-react';

interface DatabaseBlockProps {
  database: NotionDatabase;
  onUpdateDatabase: (updated: NotionDatabase) => void;
  onDeleteDatabase: (id: number) => void;
}

const COLUMN_COLORS: Record<string, string> = {
  'لم يبدأ': '#9B9A97',
  'قيد العمل': '#2EAADC',
  'جاري': '#2EAADC',
  'مكتمل': '#0F7B6C',
  'تم': '#0F7B6C',
};

export const DatabaseBlock: React.FC<DatabaseBlockProps> = ({
  database,
  onUpdateDatabase,
  onDeleteDatabase,
}) => {
  const { showToast } = useToast();
  const [name, setName] = useState(database.name);
  const [activeView, setActiveView] = useState<string>(database.views?.[0]?.type || 'table');
  const [searchQuery, setSearchQuery] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [showNewViewModal, setShowNewViewModal] = useState(false);
  const [newViewName, setNewViewName] = useState('');
  const [newViewType, setNewViewType] = useState<'table' | 'board' | 'list' | 'gallery'>('table');

  // Kanban drag state
  const [draggingRowId, setDraggingRowId] = useState<number | null>(null);
  const [dragOverCol, setDragOverCol] = useState<string | null>(null);

  const properties = database.properties || [];
  const statusProp = properties.find((p) => p.type === 'select');
  const statusPropName = statusProp ? statusProp.name : 'الحالة';
  const boardColumns = useMemo(() => {
    return statusProp && statusProp.options?.length
      ? statusProp.options
      : ['لم يبدأ', 'قيد العمل', 'مكتمل'];
  }, [statusProp]);

  // Filtered rows
  const filteredRows = useMemo(() => {
    let rows = database.rows || [];
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      rows = rows.filter((r) =>
        Object.values(r.values || {}).some((v) => String(v).toLowerCase().includes(q))
      );
    }
    if (filterStatus) {
      rows = rows.filter((r) => (r.values[statusPropName] || '') === filterStatus);
    }
    return rows;
  }, [database.rows, searchQuery, filterStatus, statusPropName]);

  // Handle database name blur
  const handleNameBlur = async () => {
    if (!name.trim() || name === database.name) return;
    try {
      const updated = await databaseService.update(database.id, { name: name.trim() });
      onUpdateDatabase(updated);
    } catch {
      showToast('فشل تحديث اسم قاعدة البيانات', 'error');
    }
  };

  // Add row
  const handleAddRow = async (initialStatus?: string) => {
    const defaultValues: Record<string, any> = {};
    if (statusProp && (initialStatus || statusProp.options?.[0])) {
      defaultValues[statusProp.name] = initialStatus || statusProp.options?.[0];
    } else if (initialStatus) {
      defaultValues[statusPropName] = initialStatus;
    }
    try {
      const row = await databaseService.addRow(database.id, { values: defaultValues });
      onUpdateDatabase({
        ...database,
        rows: [...(database.rows || []), row],
      });
    } catch {
      showToast('فشل إضافة سجل جديد', 'error');
    }
  };

  // Update row value
  const handleUpdateRowValue = async (row: DatabaseRow, propName: string, val: any) => {
    const newValues = { ...row.values, [propName]: val };
    try {
      const updatedRow = await databaseService.updateRow(row.id, {
        values: newValues,
      });
      onUpdateDatabase({
        ...database,
        rows: (database.rows || []).map((r) => (r.id === row.id ? updatedRow : r)),
      });
    } catch {
      showToast('فشل تحديث القيمة', 'error');
    }
  };

  // Delete row
  const handleDeleteRow = async (rowId: number) => {
    try {
      await databaseService.deleteRow(rowId);
      onUpdateDatabase({
        ...database,
        rows: (database.rows || []).filter((r) => r.id !== rowId),
      });
    } catch {
      showToast('فشل حذف السجل', 'error');
    }
  };

  // Add view
  const handleAddView = async () => {
    if (!newViewName.trim()) {
      showToast('يرجى كتابة اسم العرض', 'error');
      return;
    }
    try {
      const view = await databaseService.addView(database.id, {
        name: newViewName.trim(),
        type: newViewType,
      });
      onUpdateDatabase({
        ...database,
        views: [...(database.views || []), view],
      });
      setActiveView(view.type);
      setNewViewName('');
      setShowNewViewModal(false);
      showToast('تمت إضافة العرض', 'success');
    } catch {
      showToast('فشل إضافة العرض', 'error');
    }
  };

  // Handle board drop
  const handleBoardDrop = async (targetColumn: string) => {
    if (!draggingRowId) return;
    const row = (database.rows || []).find((r) => r.id === draggingRowId);
    if (!row) return;

    setDraggingRowId(null);
    setDragOverCol(null);

    if (row.values[statusPropName] === targetColumn) return;
    await handleUpdateRowValue(row, statusPropName, targetColumn);
  };

  return (
    <div className="w-full my-4 rounded-xl border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] shadow-sm select-none overflow-hidden">
      {/* 1. Database Header & Views Bar */}
      <div className="px-4 py-3 border-b border-notion-border/60 dark:border-[#2A2A2A] flex flex-wrap items-center justify-between gap-3">
        {/* Database Title */}
        <div className="flex items-center gap-2 flex-1 min-w-[200px]">
          <span className="text-lg">📊</span>
          <input
            type="text"
            dir="auto"
            value={name}
            onChange={(e) => setName(e.target.value)}
            onBlur={handleNameBlur}
            onKeyDown={(e) => {
              if (e.key === 'Enter') e.currentTarget.blur();
            }}
            placeholder="قاعدة بيانات بدون عنوان..."
            className="text-base font-bold text-notion-text dark:text-[#E3E3E0] bg-transparent border-0 p-0 focus:ring-0 focus:outline-none placeholder:text-notion-muted/40 w-full"
          />
        </div>

        {/* View Switcher Tabs & Actions */}
        <div className="flex items-center gap-1.5 flex-wrap">
          {/* View tabs */}
          <div className="flex items-center p-0.5 rounded-lg bg-notion-sidebar dark:bg-[#252525] border border-notion-border/60 dark:border-[#333333]">
            {[
              { type: 'table', label: 'جدول', icon: Table },
              { type: 'board', label: 'لوحة كانبان', icon: Kanban },
              { type: 'list', label: 'قائمة', icon: List },
              { type: 'gallery', label: 'معرض', icon: LayoutGrid },
            ].map((v) => {
              const Icon = v.icon;
              const isActive = activeView === v.type;
              return (
                <button
                  key={v.type}
                  type="button"
                  onClick={() => setActiveView(v.type)}
                  className={`flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium transition-all ${
                    isActive
                      ? 'bg-white dark:bg-[#1E1E1E] text-notion-text dark:text-[#E3E3E0] shadow-xs'
                      : 'text-notion-muted hover:text-notion-text'
                  }`}
                >
                  <Icon className="w-3.5 h-3.5" />
                  <span>{v.label}</span>
                </button>
              );
            })}
          </div>

          {/* Add Record button */}
          <button
            type="button"
            onClick={() => handleAddRow()}
            className="text-xs px-2.5 py-1 rounded-md bg-notion-text dark:bg-[#E3E3E0] text-white dark:text-[#191919] hover:opacity-90 transition-opacity font-medium flex items-center gap-1"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>سجل جديد</span>
          </button>

          {/* Delete Database Button */}
          <button
            type="button"
            onClick={() => {
              if (window.confirm('حذف قاعدة البيانات بالكامل؟')) {
                onDeleteDatabase(database.id);
              }
            }}
            className="p-1 rounded-md text-notion-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
            title="حذف قاعدة البيانات"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      {/* 2. Filter & Search Toolbar */}
      <div className="px-4 py-2 border-b border-notion-border/40 dark:border-[#2A2A2A] bg-notion-sidebar/40 dark:bg-[#1A1A1A] flex items-center justify-between gap-3 text-xs">
        <div className="flex items-center gap-2 flex-1 max-w-xs">
          <Search className="w-3.5 h-3.5 text-notion-muted shrink-0" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="بحث وتصفية السجلات..."
            className="w-full bg-transparent border-0 p-0 text-xs text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/50 focus:ring-0 focus:outline-none"
          />
        </div>

        <div className="flex items-center gap-2 shrink-0">
          <span className="text-[11px] text-notion-muted">الحالة:</span>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none cursor-pointer border border-notion-border dark:border-[#333333] rounded px-2 py-0.5"
          >
            <option value="" className="dark:bg-[#252525]">
              الكل ({database.rows?.length || 0})
            </option>
            {boardColumns.map((opt) => (
              <option key={opt} value={opt} className="dark:bg-[#252525]">
                {opt}
              </option>
            ))}
          </select>

          {(searchQuery || filterStatus) && (
            <button
              type="button"
              onClick={() => {
                setSearchQuery('');
                setFilterStatus('');
              }}
              className="text-[11px] text-notion-accent hover:underline px-1"
            >
              إلغاء الفلتر
            </button>
          )}
        </div>
      </div>

      {/* 3. VIEW 1: TABLE VIEW */}
      {activeView === 'table' && (
        <div className="overflow-x-auto">
          <table className="w-full text-right text-xs border-collapse">
            <thead>
              <tr className="border-b border-notion-border dark:border-[#2E2E2E] text-notion-muted bg-notion-sidebar/60 dark:bg-[#202020]">
                {properties.map((prop) => (
                  <th
                    key={prop.name}
                    className="px-3.5 py-2 font-medium border-l border-notion-border dark:border-[#2E2E2E] last:border-l-0"
                  >
                    {prop.name}
                  </th>
                ))}
                <th className="w-10 px-2 py-2" />
              </tr>
            </thead>
            <tbody>
              {filteredRows.length > 0 ? (
                filteredRows.map((row) => (
                  <tr
                    key={row.id}
                    className="border-b border-notion-border/40 dark:border-[#262626] last:border-b-0 hover:bg-notion-hover/40 dark:hover:bg-[#252525] group/row transition-colors"
                  >
                    {properties.map((prop) => {
                      const cellValue = row.values[prop.name] ?? '';

                      if (prop.type === 'select') {
                        return (
                          <td
                            key={prop.name}
                            className="px-3.5 py-1.5 border-l border-notion-border/40 dark:border-[#262626] last:border-l-0"
                          >
                            <select
                              value={cellValue}
                              onChange={(e) =>
                                handleUpdateRowValue(row, prop.name, e.target.value)
                              }
                              className={`px-2 py-0.5 rounded-full text-[11px] font-medium transition-colors bg-transparent border-0 cursor-pointer ${
                                cellValue
                                  ? 'bg-notion-accent/10 text-notion-accent'
                                  : 'text-notion-muted'
                              }`}
                            >
                              <option value="" className="dark:bg-[#252525]">
                                — بدون حالة
                              </option>
                              {(prop.options || []).map((opt) => (
                                <option key={opt} value={opt} className="dark:bg-[#252525]">
                                  {opt}
                                </option>
                              ))}
                            </select>
                          </td>
                        );
                      }

                      if (prop.type === 'date') {
                        return (
                          <td
                            key={prop.name}
                            className="px-3.5 py-1.5 border-l border-notion-border/40 dark:border-[#262626] last:border-l-0"
                          >
                            <input
                              type="date"
                              value={cellValue}
                              onChange={(e) =>
                                handleUpdateRowValue(row, prop.name, e.target.value)
                              }
                              className="bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                            />
                          </td>
                        );
                      }

                      return (
                        <td
                          key={prop.name}
                          className="px-3.5 py-1.5 border-l border-notion-border/40 dark:border-[#262626] last:border-l-0"
                        >
                          <input
                            type="text"
                            defaultValue={cellValue}
                            onBlur={(e) => {
                              if (e.target.value !== cellValue) {
                                handleUpdateRowValue(row, prop.name, e.target.value);
                              }
                            }}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter') e.currentTarget.blur();
                            }}
                            placeholder="اكتب..."
                            className="w-full bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/40 focus:outline-none"
                          />
                        </td>
                      );
                    })}
                    <td className="px-2 py-1.5 text-center">
                      <button
                        type="button"
                        onClick={() => handleDeleteRow(row.id)}
                        className="opacity-0 group-hover/row:opacity-100 text-notion-muted hover:text-red-500 transition-opacity p-0.5"
                        title="حذف الصف"
                      >
                        ✕
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td
                    colSpan={properties.length + 1}
                    className="px-4 py-8 text-center text-xs text-notion-muted"
                  >
                    لا توجد سجلات مطابقة للبحث
                  </td>
                </tr>
              )}
            </tbody>
          </table>

          {/* Quick Add Row footer */}
          <div className="p-2 border-t border-notion-border/40 dark:border-[#262626]">
            <button
              type="button"
              onClick={() => handleAddRow()}
              className="flex items-center gap-1.5 px-3 py-1 text-xs text-notion-muted hover:text-notion-text transition-colors rounded hover:bg-notion-hover dark:hover:bg-[#282828]"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>صف جديد</span>
            </button>
          </div>
        </div>
      )}

      {/* 4. VIEW 2: KANBAN BOARD VIEW */}
      {activeView === 'board' && (
        <div className="p-4 overflow-x-auto">
          <div className="flex gap-3 min-w-0 items-start">
            {boardColumns.map((col) => {
              const colRows = filteredRows.filter((r) => {
                const val = r.values[statusPropName];
                return val ? val === col : col === boardColumns[0];
              });
              const isDropTarget = dragOverCol === col;
              const statusColor = COLUMN_COLORS[col] || '#9B9A97';

              return (
                <div
                  key={col}
                  onDragOver={(e) => {
                    e.preventDefault();
                    if (draggingRowId) setDragOverCol(col);
                  }}
                  onDragLeave={(e) => {
                    if (dragOverCol === col) setDragOverCol(null);
                  }}
                  onDrop={(e) => {
                    e.preventDefault();
                    handleBoardDrop(col);
                  }}
                  className={`rounded-lg border border-notion-border/70 dark:border-[#2E2E2E] p-2.5 transition-all flex-1 min-w-[230px] max-w-xs min-h-[220px] ${
                    isDropTarget
                      ? 'ring-2 ring-notion-accent/60 bg-notion-accent/5'
                      : 'bg-notion-sidebar/40 dark:bg-[#1A1A1A]'
                  }`}
                >
                  {/* Column Header */}
                  <div className="flex items-center justify-between px-1 pb-2">
                    <div className="flex items-center gap-1.5">
                      <span
                        className="w-2 h-2 rounded-full shrink-0"
                        style={{ backgroundColor: statusColor }}
                      />
                      <span className="text-xs font-semibold text-notion-text dark:text-[#E3E3E0]">
                        {col}
                      </span>
                    </div>
                    <span className="text-[10px] px-1.5 py-0.2 rounded-full bg-white dark:bg-[#252525] border border-notion-border dark:border-[#333333] text-notion-muted font-medium">
                      {colRows.length}
                    </span>
                  </div>

                  {/* Cards Stack */}
                  <div className="space-y-2">
                    {colRows.map((row) => (
                      <div
                        key={row.id}
                        draggable
                        onDragStart={() => setDraggingRowId(row.id)}
                        onDragEnd={() => {
                          setDraggingRowId(null);
                          setDragOverCol(null);
                        }}
                        className={`group/card bg-white dark:bg-[#252525] rounded-lg border border-notion-border dark:border-[#333333] p-3 cursor-grab active:cursor-grabbing transition-all shadow-xs ${
                          draggingRowId === row.id
                            ? 'opacity-40 scale-95'
                            : 'hover:border-notion-border dark:hover:border-[#444444] hover:shadow-sm'
                        }`}
                      >
                        <div className="flex items-start gap-1.5 mb-1.5">
                          <textarea
                            rows={1}
                            defaultValue={row.values[properties[0]?.name] || ''}
                            onBlur={(e) =>
                              handleUpdateRowValue(row, properties[0]?.name, e.target.value)
                            }
                            onKeyDown={(e) => {
                              if (e.key === 'Enter') e.currentTarget.blur();
                            }}
                            onMouseDown={(e) => e.stopPropagation()}
                            placeholder="بدون عنوان..."
                            className="flex-1 min-w-0 bg-transparent text-xs font-medium text-notion-text dark:text-[#E3E3E0] placeholder:text-notion-muted/40 focus:outline-none resize-none overflow-hidden"
                          />
                          <button
                            type="button"
                            onClick={() => handleDeleteRow(row.id)}
                            className="opacity-0 group-hover/card:opacity-100 text-[10px] text-notion-muted hover:text-red-500 shrink-0 p-0.5 transition-opacity"
                          >
                            ✕
                          </button>
                        </div>

                        {row.values['التاريخ'] && (
                          <div className="flex items-center gap-1 text-[10px] text-notion-muted mt-1">
                            <Calendar className="w-3 h-3" />
                            <span>{row.values['التاريخ']}</span>
                          </div>
                        )}
                      </div>
                    ))}
                  </div>

                  {/* Quick Add Card at bottom of column */}
                  <button
                    type="button"
                    onClick={() => handleAddRow(col)}
                    className="w-full mt-2 flex items-center gap-1 px-2 py-1.5 rounded-md text-[11px] text-notion-muted hover:text-notion-text hover:bg-white dark:hover:bg-[#252525] transition-colors"
                  >
                    <Plus className="w-3 h-3" />
                    <span>إضافة بطاقة</span>
                  </button>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* 5. VIEW 3: LIST VIEW */}
      {activeView === 'list' && (
        <div className="divide-y divide-notion-border/40 dark:divide-[#262626]">
          {filteredRows.map((row) => (
            <div
              key={row.id}
              className="flex items-center justify-between px-4 py-2 hover:bg-notion-hover/40 dark:hover:bg-[#252525] group/row transition-colors text-xs"
            >
              <div className="flex items-center gap-3 flex-1 min-w-0">
                <span className="text-notion-muted">📄</span>
                <input
                  type="text"
                  defaultValue={row.values[properties[0]?.name] || ''}
                  onBlur={(e) =>
                    handleUpdateRowValue(row, properties[0]?.name, e.target.value)
                  }
                  className="flex-1 bg-transparent text-xs text-notion-text dark:text-[#E3E3E0] focus:outline-none"
                  placeholder="بدون عنوان..."
                />
              </div>

              <div className="flex items-center gap-3 shrink-0">
                {row.values[statusPropName] && (
                  <span className="px-2 py-0.5 rounded-full text-[10px] font-medium bg-notion-accent/10 text-notion-accent">
                    {row.values[statusPropName]}
                  </span>
                )}
                <button
                  type="button"
                  onClick={() => handleDeleteRow(row.id)}
                  className="opacity-0 group-hover/row:opacity-100 text-notion-muted hover:text-red-500 transition-opacity"
                >
                  ✕
                </button>
              </div>
            </div>
          ))}

          <div className="p-2">
            <button
              type="button"
              onClick={() => handleAddRow()}
              className="flex items-center gap-1.5 px-3 py-1 text-xs text-notion-muted hover:text-notion-text transition-colors rounded hover:bg-notion-hover dark:hover:bg-[#282828]"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>عنصر جديد</span>
            </button>
          </div>
        </div>
      )}

      {/* 6. VIEW 4: GALLERY VIEW */}
      {activeView === 'gallery' && (
        <div className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
          {filteredRows.map((row) => (
            <div
              key={row.id}
              className="rounded-lg border border-notion-border dark:border-[#2E2E2E] bg-white dark:bg-[#242424] overflow-hidden hover:shadow-sm transition-shadow group/card"
            >
              <div className="h-20 bg-gradient-to-r from-notion-accent/10 to-purple-500/10 flex items-center justify-center text-2xl select-none">
                📄
              </div>
              <div className="p-3">
                <div className="flex items-start justify-between gap-1 mb-2">
                  <input
                    type="text"
                    defaultValue={row.values[properties[0]?.name] || ''}
                    onBlur={(e) =>
                      handleUpdateRowValue(row, properties[0]?.name, e.target.value)
                    }
                    placeholder="بدون عنوان..."
                    className="w-full text-xs font-semibold text-notion-text dark:text-[#E3E3E0] bg-transparent focus:outline-none"
                  />
                  <button
                    type="button"
                    onClick={() => handleDeleteRow(row.id)}
                    className="opacity-0 group-hover/card:opacity-100 text-[10px] text-notion-muted hover:text-red-500 transition-opacity"
                  >
                    ✕
                  </button>
                </div>
                {row.values[statusPropName] && (
                  <span className="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-notion-accent/10 text-notion-accent">
                    {row.values[statusPropName]}
                  </span>
                )}
              </div>
            </div>
          ))}

          <button
            type="button"
            onClick={() => handleAddRow()}
            className="rounded-lg border border-dashed border-notion-border dark:border-[#383838] p-6 flex flex-col items-center justify-center gap-1.5 text-xs text-notion-muted hover:text-notion-text hover:border-notion-accent transition-colors"
          >
            <Plus className="w-5 h-5" />
            <span>بطاقة جديدة في المعرض</span>
          </button>
        </div>
      )}
    </div>
  );
};
