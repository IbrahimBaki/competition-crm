import { NavLink } from 'react-router-dom';
import { useVisibleNavigation } from './useVisibleNavigation';
import { useTranslation } from 'react-i18next';

export function Sidebar() {
  const navigation = useVisibleNavigation();
  const { t } = useTranslation();
  const isRTL = document.documentElement.dir === 'rtl';

  return (
    <aside className={`w-64 bg-gray-900 text-white flex flex-col ${isRTL ? 'border-l' : 'border-r'} border-gray-800`}>
      {/* Logo */}
      <div className="p-6 border-b border-gray-800">
        <h1 className="text-xl font-bold">Support CRM</h1>
      </div>

      {/* Navigation */}
      <nav className="flex-1 overflow-auto p-4 space-y-1">
        {navigation.map((item) => (
          <div key={item.id}>
            {item.children ? (
              <details className="group">
                <summary className="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-800 rounded cursor-pointer">
                  {item.icon && <span className="mr-3">{item.icon}</span>}
                  <span className="flex-1">{t(item.labelKey)}</span>
                  <span className="text-xs group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <div className="ml-6 space-y-1 mt-1">
                  {item.children.map((child) => (
                    <NavLink
                      key={child.id}
                      to={child.path}
                      className={({ isActive }) =>
                        `flex items-center px-4 py-2 rounded text-sm transition ${
                          isActive
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-400 hover:text-gray-300 hover:bg-gray-800'
                        }`
                      }
                    >
                      {child.icon && <span className="mr-2">{child.icon}</span>}
                      {t(child.labelKey)}
                    </NavLink>
                  ))}
                </div>
              </details>
            ) : (
              <NavLink
                to={item.path}
                className={({ isActive }) =>
                  `flex items-center px-4 py-2 rounded transition ${
                    isActive
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-300 hover:bg-gray-800'
                  }`
                }
              >
                {item.icon && <span className="mr-3">{item.icon}</span>}
                {t(item.labelKey)}
              </NavLink>
            )}
          </div>
        ))}
      </nav>
    </aside>
  );
}
