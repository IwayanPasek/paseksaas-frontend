import React from 'react';

export default function LogoAvatar({ logo, name, size = 'md', className = '' }) {
  const sizes = { sm: 'w-8 h-8 text-xs', md: 'w-9 h-9 text-sm', lg: 'w-10 h-10 text-base', xl: 'w-12 h-12 text-lg' };
  const s = sizes[size] || sizes.md;

  const [hasError, setHasError] = React.useState(false);

  return (
    <div className={`${s} rounded-xl bg-neutral-900 text-white flex items-center justify-center font-medium overflow-hidden shrink-0 ${className}`}>
      {logo && !hasError
        ? <img src={`/assets/img/produk/${logo}`} alt={name} onError={() => setHasError(true)} className="w-full h-full object-cover" />
        : (name?.charAt(0)?.toUpperCase() || '?')}
    </div>
  );
}
