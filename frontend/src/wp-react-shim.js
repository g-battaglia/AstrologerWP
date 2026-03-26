/**
 * Shim to map 'react' and 'react-dom' imports to WordPress's global wp.element.
 * This ensures we don't bundle React twice and use the version provided by WordPress.
 */
const wpElement = window.wp.element;

// Default export (import React from 'react')
export default wpElement;

// Named exports (import { useState } from 'react')
export const {
    Children,
    Component,
    Fragment,
    Profiler,
    PureComponent,
    StrictMode,
    Suspense,
    cloneElement,
    createContext,
    createElement,
    createFactory,
    createRef,
    forwardRef,
    isValidElement,
    lazy,
    memo,
    startTransition,
    useCallback,
    useContext,
    useDebugValue,
    useDeferredValue,
    useEffect,
    useId,
    useImperativeHandle,
    useInsertionEffect,
    useLayoutEffect,
    useMemo,
    useReducer,
    useRef,
    useState,
    useSyncExternalStore,
    useTransition,
    version,
    // ReactDOM exports usually available in wp.element in modern WP
    createRoot,
    hydrateRoot,
    render,
    flushSync,
    createPortal,
    unmountComponentAtNode,
} = wpElement;

function normalizeChildren(children) {
    if (children === undefined) return [];
    return Array.isArray(children) ? children : [children];
}

function createElementFromJsx(type, props, key) {
    const rawProps = props ?? {};
    const { children, ...rest } = rawProps;

    const newProps = { ...rest };
    if (key !== undefined && key !== null) {
        newProps.key = key;
    }

    const childArgs = normalizeChildren(children);
    return wpElement.createElement(type, newProps, ...childArgs);
}

// Runtime exports for react/jsx-runtime support
export const jsx = wpElement.jsx || ((type, props, key) => createElementFromJsx(type, props, key));
export const jsxs = wpElement.jsxs || ((type, props, key) => createElementFromJsx(type, props, key));

// Runtime exports for react/jsx-dev-runtime support
export const jsxDEV =
    wpElement.jsxDEV ||
    function (type, props, key /* isStaticChildren, source, self */) {
        return createElementFromJsx(type, props, key);
    };
