import React from 'react';

export default function EmptyState({ icon: Icon, title, subtitle }) {
  return (
    <div className="text-center py-16 card rounded-xl">
      {Icon && <Icon size={40} className="mx-auto text-neutral-300 mb-3" />}
      <p className="font-medium text-neutral-500">{title}</p>
      {subtitle && <p className="text-neutral-400 text-sm mt-1">{subtitle}</p>}
    </div>
  );
}
