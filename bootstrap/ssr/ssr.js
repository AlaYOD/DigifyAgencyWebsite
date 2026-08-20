import { jsxs, jsx, Fragment } from "react/jsx-runtime";
import { createLayoutPropsStore, config as config$1, isUrlMethodPair, resolveUrlMethodPairComponent, mergeDataIntoQueryString, getScrollableParent, useInfiniteScroll, router, UseFormUtils, formDataToObject, FormComponentResetSymbol, resetFormFields, shouldIntercept, shouldNavigate, http, exposeInterceptors, buildSSRBody, getInitialPageFromDOM, setupProgress, createHeadManager, resolveServerHead, isPropsObjectOrCallback, isPropsObject, normalizeLayouts } from "@inertiajs/core";
import React, { createContext, forwardRef, useRef, useMemo, useState, useEffect, useImperativeHandle, createElement, useCallback, use, useSyncExternalStore, StrictMode, isValidElement } from "react";
import { flushSync } from "react-dom";
import { hydrateRoot, createRoot } from "react-dom/client";
import { set, has, get, escape } from "es-toolkit/compat";
import { cloneDeep, isEqual } from "es-toolkit";
import { createValidator, toSimpleValidationErrors, resolveName } from "laravel-precognition";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm as useForm$1 } from "react-hook-form";
import { z } from "zod";
import createServer from "@inertiajs/core/server";
import { renderToString } from "react-dom/server";
var headContext = createContext(null);
headContext.displayName = "InertiaHeadContext";
var HeadContext_default = headContext;
var store = createLayoutPropsStore();
function resetLayoutProps() {
  store.reset();
}
var pageContext = createContext(null);
pageContext.displayName = "InertiaPageContext";
var PageContext_default = pageContext;
function isComponent(value) {
  return typeof value === "function" || typeof value === "object" && value !== null && "$$typeof" in value;
}
function isRenderFunction(value) {
  if (typeof value !== "function") {
    return false;
  }
  const fn = value;
  return fn.length === 1 && typeof fn.prototype === "undefined";
}
function isLayoutResolver(value) {
  return typeof value === "function" && value.length <= 1 && typeof value.prototype === "undefined";
}
var currentIsInitialPage = true;
var routerIsInitialized = false;
var swapComponent = async () => {
  currentIsInitialPage = false;
};
var emptySnapshot = {
  shared: {},
  named: {}
};
function App({
  children,
  initialPage,
  initialComponent,
  resolveComponent,
  titleCallback,
  onHeadUpdate,
  defaultLayout,
  serverHead
}) {
  const [current, setCurrent] = useState({
    component: initialComponent || null,
    page: { ...initialPage, flash: initialPage.flash ?? {} },
    key: null
  });
  const pageRef = useRef(current.page);
  pageRef.current = current.page;
  const headManager = useMemo(() => {
    return createHeadManager(
      typeof window === "undefined",
      (title) => titleCallback ? titleCallback(title, pageRef.current) : title,
      onHeadUpdate || (() => {
      }),
      resolveServerHead(initialPage, serverHead)
    );
  }, []);
  const dynamicLayoutProps = useSyncExternalStore(store.subscribe, store.get, () => emptySnapshot);
  if (!routerIsInitialized) {
    router.init({
      initialPage,
      resolveComponent,
      swapComponent: async (args) => swapComponent(args),
      onFlash: (flash) => {
        setCurrent((current2) => ({
          ...current2,
          page: { ...current2.page, flash }
        }));
      }
    });
    routerIsInitialized = true;
  }
  useEffect(() => {
    swapComponent = async ({ component, page, preserveState }) => {
      if (currentIsInitialPage) {
        currentIsInitialPage = false;
        return;
      }
      if (!preserveState) {
        resetLayoutProps();
      }
      flushSync(
        () => setCurrent((current2) => ({
          component,
          page,
          key: preserveState ? current2.key : Date.now()
        }))
      );
    };
    const syncServerHead = (event) => {
      headManager.updateServerHead(resolveServerHead(event.detail.page, serverHead));
    };
    const removeNavigateListener = router.on("navigate", syncServerHead);
    const removeClientVisitListener = router.on("clientVisit", syncServerHead);
    return () => {
      removeNavigateListener();
      removeClientVisitListener();
    };
  }, []);
  if (!current.component) {
    return createElement(
      HeadContext_default.Provider,
      { value: headManager },
      createElement(PageContext_default.Provider, { value: current.page }, null)
    );
  }
  const renderChildren = children || (({ Component, props, key }) => {
    const child = createElement(Component, { key, ...props });
    let effectiveLayout;
    let callbackProps = null;
    const layoutValue = Component.layout;
    if (isLayoutResolver(layoutValue)) {
      const result = layoutValue(props);
      if (isValidElement(result)) {
        return layoutValue(child);
      }
      if (isPropsObjectOrCallback(result, isComponent)) {
        effectiveLayout = defaultLayout?.(current.page.component, current.page);
        callbackProps = result;
      } else {
        effectiveLayout = result;
      }
    } else if (isPropsObject(layoutValue, isComponent)) {
      effectiveLayout = defaultLayout?.(current.page.component, current.page);
      callbackProps = layoutValue;
    } else {
      effectiveLayout = layoutValue ?? defaultLayout?.(current.page.component, current.page);
    }
    let layouts = normalizeLayouts(
      effectiveLayout,
      isComponent,
      layoutValue && !callbackProps ? isRenderFunction : void 0
    );
    if (callbackProps) {
      layouts = layouts.map((l) => ({ ...l, props: { ...l.props, ...callbackProps } }));
    }
    if (layouts.length > 0) {
      return layouts.reduceRight((childNode, layout) => {
        return createElement(
          layout.component,
          {
            ...props,
            ...layout.props,
            ...dynamicLayoutProps.shared,
            ...layout.name ? dynamicLayoutProps.named[layout.name] || {} : {}
          },
          childNode
        );
      }, child);
    }
    return child;
  });
  return createElement(
    HeadContext_default.Provider,
    { value: headManager },
    createElement(
      PageContext_default.Provider,
      { value: current.page },
      renderChildren({
        Component: current.component,
        key: current.key,
        props: current.page.props
      })
    )
  );
}
App.displayName = "Inertia";
async function createInertiaApp({
  id = "app",
  resolve,
  setup,
  title,
  progress: progress2 = {},
  page,
  render,
  defaults = {},
  nonce,
  http: http3,
  layout,
  serverHead,
  strictMode = false,
  withApp,
  dev = false
} = {}) {
  config.replace(defaults);
  if (nonce) {
    config.set("nonce", nonce);
  }
  if (http3) {
    http.setClient(http3);
  }
  if (dev) {
    exposeInterceptors();
  }
  const isServer = typeof window === "undefined";
  const wrapWithStrictMode = (element) => {
    return strictMode ? createElement(StrictMode, null, element) : element;
  };
  const resolveComponent = (name, page2) => Promise.resolve(resolve(name, page2)).then((module) => {
    return module.default || module;
  });
  if (isServer && !page && !render) {
    return async (page2, renderToString2) => {
      let head2 = [];
      const initialComponent = await resolveComponent(page2.component, page2);
      const props = {
        initialPage: page2,
        initialComponent,
        resolveComponent,
        titleCallback: title,
        onHeadUpdate: (elements) => head2 = elements,
        defaultLayout: layout,
        serverHead
      };
      let reactApp2;
      if (setup) {
        reactApp2 = setup({
          el: null,
          App,
          props
        });
      } else {
        reactApp2 = wrapWithStrictMode(createElement(App, props));
        if (withApp) {
          reactApp2 = withApp(reactApp2, { ssr: true, page: page2 });
        }
      }
      const html = renderToString2(reactApp2);
      const body = buildSSRBody(id, page2, html);
      return { head: head2, body };
    };
  }
  const initialPage = page || getInitialPageFromDOM(id);
  let head = [];
  const reactApp = await Promise.all([
    resolveComponent(initialPage.component, initialPage),
    router.decryptHistory().catch(() => {
    })
  ]).then(([initialComponent]) => {
    const props = {
      initialPage,
      initialComponent,
      resolveComponent,
      titleCallback: title,
      onHeadUpdate: isServer ? (elements) => head = elements : void 0,
      defaultLayout: layout,
      serverHead
    };
    if (isServer) {
      return setup({
        el: null,
        App,
        props
      });
    }
    const el = document.getElementById(id);
    if (setup) {
      return setup({
        el,
        App,
        props
      });
    }
    let appElement = wrapWithStrictMode(createElement(App, props));
    if (withApp) {
      appElement = withApp(appElement, { ssr: false, page: initialPage });
    }
    if (el.hasAttribute("data-server-rendered")) {
      hydrateRoot(el, appElement);
    } else {
      createRoot(el).render(appElement);
    }
  });
  if (!isServer && progress2) {
    setupProgress(progress2);
  }
  if (isServer && render && reactApp) {
    const html = render(reactApp);
    const body = buildSSRBody(id, initialPage, html);
    return { head, body };
  }
}
function usePage() {
  const page = use(PageContext_default);
  if (!page) {
    throw new Error("usePage must be used within the Inertia component");
  }
  return page;
}
function useFormState(options) {
  const { data: dataOption, useDataState, useErrorsState } = options;
  const isDataFunction = typeof dataOption === "function";
  const resolveData = () => isDataFunction ? dataOption() : dataOption;
  const initialData = cloneDeep(resolveData());
  const isMounted = useRef(false);
  const precognitionEndpointRef = useRef(options.precognitionEndpoint ?? null);
  const [defaults, setDefaultsState] = useState(cloneDeep(initialData));
  const [data, setData] = useDataState ? useDataState() : useState(cloneDeep(initialData));
  const [errors, setErrors] = useErrorsState ? useErrorsState() : useState({});
  const [processing, setProcessing] = useState(false);
  const [progress2, setProgress] = useState(null);
  const [wasSuccessful, setWasSuccessful] = useState(false);
  const [recentlySuccessful, setRecentlySuccessful] = useState(false);
  const recentlySuccessfulTimeoutId = useRef(void 0);
  const transformRef = useRef((data2) => data2);
  const defaultsCalledInOnSuccessRef = useRef(false);
  const validatorRef = useRef(null);
  const [validating, setValidating] = useState(false);
  const [touchedFields, setTouchedFields] = useState([]);
  const [validFields, setValidFields] = useState([]);
  const withAllErrorsRef = useRef(null);
  const withAllErrorsEnabled = () => withAllErrorsRef.current ?? config.get("form.withAllErrors");
  const dataRef = useRef(data);
  useEffect(() => {
    dataRef.current = data;
  });
  useEffect(() => {
    isMounted.current = true;
    return () => {
      isMounted.current = false;
    };
  }, []);
  const commitData = useCallback(
    (next) => {
      dataRef.current = next;
      setData(next);
    },
    [setData]
  );
  const setDataFunction = useCallback(
    (keyOrData, maybeValue) => {
      if (typeof keyOrData === "string") {
        commitData(set(cloneDeep(dataRef.current), keyOrData, maybeValue));
      } else if (typeof keyOrData === "function") {
        commitData(keyOrData(dataRef.current));
      } else {
        commitData(keyOrData);
      }
    },
    [commitData]
  );
  const setDefaultsFunction = useCallback(
    (fieldOrFields, maybeValue) => {
      if (isDataFunction) {
        throw new Error("You cannot call `defaults()` when using a function to define your form data.");
      }
      defaultsCalledInOnSuccessRef.current = true;
      let newDefaults = {};
      if (typeof fieldOrFields === "undefined") {
        newDefaults = { ...dataRef.current };
        setDefaultsState(dataRef.current);
      } else {
        setDefaultsState((defaults2) => {
          newDefaults = typeof fieldOrFields === "string" ? set(cloneDeep(defaults2), fieldOrFields, maybeValue) : Object.assign(cloneDeep(defaults2), fieldOrFields);
          return newDefaults;
        });
      }
      validatorRef.current?.defaults(newDefaults);
    },
    [setDefaultsState]
  );
  const reset = useCallback(
    (...fields) => {
      const resolvedData = isDataFunction ? cloneDeep(resolveData()) : defaults;
      const clonedData = cloneDeep(resolvedData);
      if (fields.length === 0) {
        if (isDataFunction) {
          setDefaultsState(clonedData);
        }
        commitData(clonedData);
      } else {
        if (isDataFunction) {
          setDefaultsState((currentDefaults) => {
            const newDefaults = cloneDeep(currentDefaults);
            fields.filter((key) => has(clonedData, key)).forEach((key) => {
              set(newDefaults, key, get(clonedData, key));
            });
            return newDefaults;
          });
        }
        const next = fields.filter((key) => has(clonedData, key)).reduce(
          (carry, key) => {
            return set(carry, key, get(clonedData, key));
          },
          { ...dataRef.current }
        );
        commitData(next);
      }
      validatorRef.current?.reset(...fields);
    },
    [commitData, defaults]
  );
  const setError = useCallback(
    (fieldOrFields, maybeValue) => {
      setErrors((errors2) => {
        const newErrors = {
          ...errors2,
          ...typeof fieldOrFields === "string" ? { [fieldOrFields]: maybeValue } : fieldOrFields
        };
        validatorRef.current?.setErrors(newErrors);
        return newErrors;
      });
    },
    [setErrors]
  );
  const clearErrors = useCallback(
    (...fields) => {
      setErrors((errors2) => {
        const newErrors = Object.keys(errors2).reduce(
          (carry, field) => ({
            ...carry,
            ...fields.length > 0 && !fields.includes(field) ? { [field]: errors2[field] } : {}
          }),
          {}
        );
        if (validatorRef.current) {
          if (fields.length === 0) {
            validatorRef.current.setErrors({});
          } else {
            fields.forEach(validatorRef.current.forgetError);
          }
        }
        return newErrors;
      });
    },
    [setErrors]
  );
  const resetAndClearErrors = useCallback(
    (...fields) => {
      reset(...fields);
      clearErrors(...fields);
    },
    [reset, clearErrors]
  );
  const markAsSuccessful = useCallback(() => {
    clearErrors();
    setWasSuccessful(true);
    setRecentlySuccessful(true);
    recentlySuccessfulTimeoutId.current = window.setTimeout(() => {
      if (isMounted.current) {
        setRecentlySuccessful(false);
      }
    }, config.get("form.recentlySuccessfulDuration"));
  }, [clearErrors, setWasSuccessful, setRecentlySuccessful]);
  const resetBeforeSubmit = useCallback(() => {
    setWasSuccessful(false);
    setRecentlySuccessful(false);
    clearTimeout(recentlySuccessfulTimeoutId.current);
  }, [setWasSuccessful, setRecentlySuccessful]);
  const finishProcessing = useCallback(() => {
    setProcessing(false);
    setProgress(null);
  }, [setProcessing, setProgress]);
  const transformFunction = useCallback((callback) => {
    transformRef.current = callback;
  }, []);
  const tap = (value, callback) => {
    callback(value);
    return value;
  };
  const valid = useCallback(
    (field) => validFields.includes(field),
    [validFields]
  );
  const invalid = useCallback((field) => field in errors, [errors]);
  const touched = useCallback(
    (field) => typeof field === "string" ? touchedFields.includes(field) : touchedFields.length > 0,
    [touchedFields]
  );
  const form = {
    data,
    isDirty: !isEqual(data, defaults),
    errors,
    hasErrors: Object.keys(errors).length > 0,
    processing,
    progress: progress2,
    wasSuccessful,
    recentlySuccessful,
    setData: setDataFunction,
    transform: transformFunction,
    setDefaults: setDefaultsFunction,
    reset,
    setError,
    clearErrors,
    resetAndClearErrors
  };
  const validate = (field, config3) => {
    if (typeof field === "object" && !("target" in field)) {
      config3 = field;
      field = void 0;
    }
    if (field === void 0) {
      validatorRef.current.validate(config3);
    } else {
      const fieldName = resolveName(field);
      const transformedData = transformRef.current(dataRef.current);
      validatorRef.current.validate(fieldName, get(transformedData, fieldName), config3);
    }
    return form;
  };
  const withPrecognition = (...args) => {
    precognitionEndpointRef.current = UseFormUtils.createWayfinderCallback(...args);
    if (!validatorRef.current) {
      const validator = createValidator(
        (client) => {
          const { method, url } = precognitionEndpointRef.current();
          const currentData = dataRef.current;
          const transformedData = transformRef.current(currentData);
          return client[method](url, transformedData);
        },
        cloneDeep(defaults)
      );
      validatorRef.current = validator;
      validator.on("validatingChanged", () => {
        setValidating(validator.validating());
      }).on("validatedChanged", () => {
        setValidFields(validator.valid());
      }).on("touchedChanged", () => {
        setTouchedFields(validator.touched());
      }).on("errorsChanged", () => {
        const validationErrors = withAllErrorsEnabled() ? validator.errors() : toSimpleValidationErrors(validator.errors());
        setErrors(validationErrors);
        setValidFields(validator.valid());
      });
    }
    const precognitiveForm = Object.assign(form, {
      validating,
      validator: () => validatorRef.current,
      valid,
      invalid,
      touched,
      withoutFileValidation: () => tap(precognitiveForm, () => validatorRef.current?.withoutFileValidation()),
      touch: (field, ...fields) => {
        if (Array.isArray(field)) {
          validatorRef.current?.touch(field);
        } else if (typeof field === "string") {
          validatorRef.current?.touch([field, ...fields]);
        } else {
          validatorRef.current?.touch(field);
        }
        return precognitiveForm;
      },
      withAllErrors: () => tap(precognitiveForm, () => withAllErrorsRef.current = true),
      setValidationTimeout: (duration) => tap(precognitiveForm, () => validatorRef.current?.setTimeout(duration)),
      validateFiles: () => tap(precognitiveForm, () => validatorRef.current?.validateFiles()),
      validate,
      setErrors: (errors2) => tap(precognitiveForm, () => form.setError(errors2)),
      forgetError: (field) => tap(
        precognitiveForm,
        () => form.clearErrors(resolveName(field))
      )
    });
    return precognitiveForm;
  };
  form.withPrecognition = withPrecognition;
  if (precognitionEndpointRef.current) {
    form.withPrecognition(precognitionEndpointRef.current);
  }
  return {
    form,
    setDefaultsState,
    transformRef,
    precognitionEndpointRef,
    dataRef,
    isMounted,
    setProcessing,
    setProgress,
    markAsSuccessful,
    clearErrors,
    setError,
    defaultsCalledInOnSuccessRef,
    resetBeforeSubmit,
    finishProcessing,
    withAllErrors: {
      enabled: withAllErrorsEnabled,
      enable: () => {
        withAllErrorsRef.current = true;
      }
    }
  };
}
function useRemember(initialState, key, excludeKeysRef) {
  const [state, setState] = useState(() => {
    const restored = router.restore(key);
    return restored !== void 0 ? restored : initialState;
  });
  useEffect(() => {
    const keys = excludeKeysRef?.current;
    if (keys && keys.length > 0 && typeof state === "object" && state !== null) {
      const filtered = { ...state };
      keys.forEach((k) => delete filtered[k]);
      router.remember(filtered, key);
    } else {
      router.remember(state, key);
    }
  }, [state, key]);
  return [state, setState];
}
function useForm(...args) {
  const { rememberKey, data, precognitionEndpoint } = UseFormUtils.parseUseFormArguments(...args);
  const initialDefaults = typeof data === "function" ? cloneDeep(data()) : cloneDeep(data);
  const cancelToken = useRef(null);
  const excludeKeysRef = useRef([]);
  const pendingOptimisticRef = useRef(null);
  const useDataState = rememberKey ? () => useRemember(initialDefaults, `${rememberKey}:data`, excludeKeysRef) : void 0;
  const useErrorsState = rememberKey ? () => useRemember({}, `${rememberKey}:errors`) : void 0;
  const {
    form: baseForm,
    setDefaultsState,
    transformRef,
    precognitionEndpointRef,
    dataRef,
    isMounted,
    setProcessing,
    setProgress,
    markAsSuccessful,
    clearErrors,
    setError,
    defaultsCalledInOnSuccessRef,
    resetBeforeSubmit,
    finishProcessing
  } = useFormState({
    data,
    precognitionEndpoint,
    useDataState,
    useErrorsState
  });
  const submit = useCallback(
    (...args2) => {
      const { method, url, options } = UseFormUtils.parseSubmitArguments(args2, precognitionEndpointRef.current);
      defaultsCalledInOnSuccessRef.current = false;
      const _options = {
        ...options,
        onCancelToken: (token) => {
          cancelToken.current = token;
          return options.onCancelToken?.(token);
        },
        onBefore: (visit) => {
          resetBeforeSubmit();
          return options.onBefore?.(visit);
        },
        onStart: (visit) => {
          setProcessing(true);
          return options.onStart?.(visit);
        },
        onProgress: (event) => {
          setProgress(event || null);
          return options.onProgress?.(event);
        },
        onSuccess: async (page) => {
          if (isMounted.current) {
            markAsSuccessful();
          }
          const onSuccess = options.onSuccess ? await options.onSuccess(page) : null;
          if (isMounted.current && !defaultsCalledInOnSuccessRef.current) {
            baseForm.setData((data2) => {
              setDefaultsState(cloneDeep(data2));
              return data2;
            });
          }
          return onSuccess;
        },
        onError: (errors) => {
          if (isMounted.current) {
            clearErrors();
            setError(errors);
          }
          return options.onError?.(errors);
        },
        onCancel: () => {
          return options.onCancel?.();
        },
        onFinish: (visit) => {
          if (isMounted.current) {
            finishProcessing();
          }
          cancelToken.current = null;
          return options.onFinish?.(visit);
        }
      };
      _options.optimistic = _options.optimistic ?? pendingOptimisticRef.current ?? void 0;
      pendingOptimisticRef.current = null;
      const transformedData = transformRef.current(dataRef.current);
      if (method === "delete") {
        router.delete(url, { ..._options, data: transformedData });
      } else {
        router[method](url, transformedData, _options);
      }
    },
    [clearErrors, setError, transformRef]
  );
  const cancel = useCallback(() => {
    if (cancelToken.current) {
      cancelToken.current.cancel();
    }
  }, []);
  const submitMethods = useMemo(
    () => ({
      get: (url, options = {}) => submit("get", url, options),
      post: (url, options = {}) => submit("post", url, options),
      put: (url, options = {}) => submit("put", url, options),
      patch: (url, options = {}) => submit("patch", url, options),
      delete: (url, options = {}) => submit("delete", url, options)
    }),
    [submit]
  );
  Object.assign(baseForm, {
    submit,
    ...submitMethods,
    cancel,
    dontRemember: (...keys) => {
      excludeKeysRef.current = keys;
      return form;
    },
    optimistic: (callback) => {
      pendingOptimisticRef.current = callback;
      return form;
    }
  });
  const form = baseForm;
  const originalWithPrecognition = baseForm.withPrecognition;
  form.withPrecognition = (...args2) => {
    originalWithPrecognition(...args2);
    return form;
  };
  return precognitionEndpointRef.current ? form : form;
}
var deferStateUpdate = (callback) => {
  typeof React.startTransition === "function" ? React.startTransition(callback) : setTimeout(callback, 0);
};
var noop = () => void 0;
var FormContext = createContext(void 0);
var Form = forwardRef(
  ({
    action = "",
    method = "get",
    headers = {},
    queryStringArrayFormat = "brackets",
    errorBag = null,
    showProgress = true,
    transform = (data) => data,
    optimistic,
    options = {},
    onStart = noop,
    onProgress = noop,
    onFinish = noop,
    onBefore = noop,
    onCancel = noop,
    onSuccess = noop,
    onError = noop,
    onCancelToken = noop,
    onSubmitComplete = noop,
    disableWhileProcessing = false,
    resetOnError = false,
    resetOnSuccess = false,
    setDefaultsOnSuccess = false,
    invalidateCacheTags = [],
    validateFiles = false,
    validationTimeout = 1500,
    withAllErrors = null,
    component = null,
    instant = false,
    children,
    ...props
  }, ref) => {
    const getTransformedData = () => {
      const [_url, data] = getUrlAndData();
      return transform(data);
    };
    const form = useForm({}).withPrecognition(
      () => resolvedMethod,
      () => getUrlAndData()[0]
    ).setValidationTimeout(validationTimeout);
    if (validateFiles) {
      form.validateFiles();
    }
    if (withAllErrors ?? config$1.get("form.withAllErrors")) {
      form.withAllErrors();
    }
    form.transform(getTransformedData);
    const formElement = useRef(void 0);
    const resolvedMethod = useMemo(() => {
      return isUrlMethodPair(action) ? action.method : method.toLowerCase();
    }, [action, method]);
    const resolvedComponent = useMemo(() => {
      if (component) {
        return component;
      }
      if (instant && isUrlMethodPair(action)) {
        return resolveUrlMethodPairComponent(action);
      }
      return null;
    }, [component, instant, action]);
    const [isDirty, setIsDirty] = useState(false);
    const defaultData = useRef(new FormData());
    const getFormData = (submitter) => new FormData(formElement.current, submitter);
    const getData = (submitter) => formDataToObject(getFormData(submitter));
    const getUrlAndData = (submitter) => {
      return mergeDataIntoQueryString(
        resolvedMethod,
        isUrlMethodPair(action) ? action.url : action,
        getData(submitter),
        queryStringArrayFormat
      );
    };
    const updateDirtyState = (event) => {
      if (event.type === "reset" && event.detail?.[FormComponentResetSymbol]) {
        event.preventDefault();
      }
      deferStateUpdate(
        () => setIsDirty(event.type === "reset" ? false : !isEqual(getData(), formDataToObject(defaultData.current)))
      );
    };
    const clearErrors = (...names) => {
      form.clearErrors(...names);
      return form;
    };
    useEffect(() => {
      defaultData.current = getFormData();
      form.setDefaults(getData());
      const formEvents = ["input", "change", "reset"];
      formEvents.forEach((e) => formElement.current.addEventListener(e, updateDirtyState));
      return () => {
        formEvents.forEach((e) => formElement.current?.removeEventListener(e, updateDirtyState));
      };
    }, []);
    useEffect(() => {
      form.setValidationTimeout(validationTimeout);
    }, [validationTimeout]);
    useEffect(() => {
      if (validateFiles) {
        form.validateFiles();
      } else {
        form.withoutFileValidation();
      }
    }, [validateFiles]);
    const reset = (...fields) => {
      if (formElement.current) {
        resetFormFields(formElement.current, defaultData.current, fields);
      }
      form.reset(...fields);
    };
    const resetAndClearErrors = (...fields) => {
      clearErrors(...fields);
      reset(...fields);
    };
    const maybeReset = (resetOption) => {
      if (!resetOption) {
        return;
      }
      if (resetOption === true) {
        reset();
      } else if (resetOption.length > 0) {
        reset(...resetOption);
      }
    };
    const submit = (submitter) => {
      const [url, data] = getUrlAndData(submitter);
      const formTarget = submitter?.getAttribute("formtarget");
      if (formTarget === "_blank" && resolvedMethod === "get") {
        window.open(url, "_blank");
        return;
      }
      const submitOptions = {
        headers,
        queryStringArrayFormat,
        errorBag,
        showProgress,
        invalidateCacheTags,
        component: resolvedComponent,
        optimistic: optimistic ? (pageProps) => optimistic(pageProps, data) : void 0,
        onCancelToken,
        onBefore,
        onStart,
        onProgress,
        onFinish,
        onCancel,
        onSuccess: async (...args) => {
          const result = await onSuccess(...args);
          onSubmitComplete({
            reset,
            defaults
          });
          maybeReset(resetOnSuccess);
          if (setDefaultsOnSuccess === true) {
            defaults();
          }
          return result;
        },
        onError(...args) {
          onError(...args);
          maybeReset(resetOnError);
        },
        ...options
      };
      form.transform(() => transform(data));
      form.submit(resolvedMethod, url, submitOptions);
      form.transform(getTransformedData);
    };
    const defaults = () => {
      defaultData.current = getFormData();
      setIsDirty(false);
    };
    const exposed = {
      errors: form.errors,
      hasErrors: form.hasErrors,
      processing: form.processing,
      progress: form.progress,
      wasSuccessful: form.wasSuccessful,
      recentlySuccessful: form.recentlySuccessful,
      isDirty,
      clearErrors,
      resetAndClearErrors,
      setError: form.setError,
      reset,
      submit,
      defaults,
      getData,
      getFormData,
      // Precognition
      validator: () => form.validator(),
      validating: form.validating,
      valid: form.valid,
      invalid: form.invalid,
      validate: (field, config3) => form.validate(...UseFormUtils.mergeHeadersForValidation(field, config3, headers)),
      touch: form.touch,
      touched: form.touched
    };
    useImperativeHandle(ref, () => exposed, [form, isDirty, submit]);
    const formNode = createElement(
      "form",
      {
        ...props,
        ref: formElement,
        action: isUrlMethodPair(action) ? action.url : action,
        method: resolvedMethod,
        onSubmit: (event) => {
          event.preventDefault();
          submit(event.nativeEvent.submitter);
        },
        inert: disableWhileProcessing && form.processing
      },
      typeof children === "function" ? children(exposed) : children
    );
    return createElement(FormContext.Provider, { value: exposed }, formNode);
  }
);
Form.displayName = "InertiaForm";
var Head = function({ children, title }) {
  const headManager = use(HeadContext_default);
  const provider = useMemo(() => headManager.createProvider(), [headManager]);
  const isServer = typeof window === "undefined";
  useEffect(() => {
    provider.reconnect();
    provider.update(renderNodes(children));
    return () => {
      provider.disconnect();
    };
  }, [provider, children, title]);
  function isUnaryTag(node) {
    return typeof node.type === "string" && [
      "area",
      "base",
      "br",
      "col",
      "embed",
      "hr",
      "img",
      "input",
      "keygen",
      "link",
      "meta",
      "param",
      "source",
      "track",
      "wbr"
    ].indexOf(node.type) > -1;
  }
  function renderTagStart(node) {
    const attrs = Object.keys(node.props).reduce((carry, name) => {
      if (["head-key", "children", "dangerouslySetInnerHTML"].includes(name)) {
        return carry;
      }
      const value = String(node.props[name]);
      if (value === "") {
        return carry + ` ${name}`;
      }
      return carry + ` ${name}="${escape(value)}"`;
    }, "");
    return `<${String(node.type)}${attrs}>`;
  }
  function renderTagChildren(node) {
    const { children: children2 } = node.props;
    if (typeof children2 === "string") {
      return children2;
    }
    if (Array.isArray(children2)) {
      return children2.reduce((html, child) => html + renderTag(child), "");
    }
    return "";
  }
  function renderTag(node) {
    let html = renderTagStart(node);
    if (node.props.children) {
      html += renderTagChildren(node);
    }
    if (node.props.dangerouslySetInnerHTML) {
      html += node.props.dangerouslySetInnerHTML.__html;
    }
    if (!isUnaryTag(node)) {
      html += `</${String(node.type)}>`;
    }
    return html;
  }
  function ensureNodeHasInertiaProp(node) {
    return React.cloneElement(node, {
      "data-inertia": node.props["head-key"] !== void 0 ? node.props["head-key"] : ""
    });
  }
  function renderNode(node) {
    return renderTag(ensureNodeHasInertiaProp(node));
  }
  function renderNodes(nodes) {
    const elements = React.Children.toArray(nodes).filter((node) => node).map((node) => renderNode(node));
    if (title && !elements.find((tag) => tag.startsWith("<title"))) {
      elements.push(`<title data-inertia="">${escape(title)}</title>`);
    }
    return elements;
  }
  if (isServer) {
    provider.update(renderNodes(children));
  }
  return null;
};
var Head_default = Head;
var resolveHTMLElement = (value, fallback) => {
  if (!value) {
    return fallback;
  }
  if (value && typeof value === "object" && "current" in value) {
    return value.current;
  }
  if (typeof value === "string") {
    return document.querySelector(value);
  }
  return fallback;
};
var renderSlot = (slotContent, slotProps, fallback = null) => {
  if (!slotContent) {
    return fallback;
  }
  return typeof slotContent === "function" ? slotContent(slotProps) : slotContent;
};
var InfiniteScroll = forwardRef(
  ({
    data,
    buffer = 0,
    as = "div",
    manual = false,
    manualAfter = 0,
    preserveUrl = false,
    reverse = false,
    autoScroll,
    children,
    startElement,
    endElement,
    itemsElement,
    previous,
    next,
    loading,
    params = {},
    onlyNext = false,
    onlyPrevious = false,
    ...props
  }, ref) => {
    const [startElementFromRef, setStartElementFromRef] = useState(null);
    const startElementRef = useCallback((node) => setStartElementFromRef(node), []);
    const [endElementFromRef, setEndElementFromRef] = useState(null);
    const endElementRef = useCallback((node) => setEndElementFromRef(node), []);
    const [itemsElementFromRef, setItemsElementFromRef] = useState(null);
    const itemsElementRef = useCallback((node) => setItemsElementFromRef(node), []);
    const scrollProp = usePage().scrollProps?.[data];
    const [loadingPrevious, setLoadingPrevious] = useState(false);
    const [loadingNext, setLoadingNext] = useState(false);
    const [requestCount, setRequestCount] = useState(0);
    const [hasPreviousPage, setHasPreviousPage] = useState(!!scrollProp?.previousPage);
    const [hasNextPage, setHasNextPage] = useState(!!scrollProp?.nextPage);
    const [resolvedStartElement, setResolvedStartElement] = useState(null);
    const [resolvedEndElement, setResolvedEndElement] = useState(null);
    const [resolvedItemsElement, setResolvedItemsElement] = useState(null);
    useEffect(() => {
      const element = startElement ? resolveHTMLElement(startElement, startElementFromRef) : startElementFromRef;
      setResolvedStartElement(element);
    }, [startElement, startElementFromRef]);
    useEffect(() => {
      const element = endElement ? resolveHTMLElement(endElement, endElementFromRef) : endElementFromRef;
      setResolvedEndElement(element);
    }, [endElement, endElementFromRef]);
    useEffect(() => {
      const element = itemsElement ? resolveHTMLElement(itemsElement, itemsElementFromRef) : itemsElementFromRef;
      setResolvedItemsElement(element);
    }, [itemsElement, itemsElementFromRef]);
    const scrollableParent = useMemo(() => getScrollableParent(resolvedItemsElement), [resolvedItemsElement]);
    const callbackPropsRef = useRef({
      buffer,
      onlyNext,
      onlyPrevious,
      reverse,
      preserveUrl,
      params
    });
    callbackPropsRef.current = {
      buffer,
      onlyNext,
      onlyPrevious,
      reverse,
      preserveUrl,
      params
    };
    const [infiniteScroll, setInfiniteScroll] = useState(null);
    const dataManager = useMemo(() => infiniteScroll?.dataManager, [infiniteScroll]);
    const elementManager = useMemo(() => infiniteScroll?.elementManager, [infiniteScroll]);
    const scrollToBottom = useCallback(() => {
      if (scrollableParent) {
        scrollableParent.scrollTo({
          top: scrollableParent.scrollHeight,
          behavior: "instant"
        });
      } else {
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: "instant"
        });
      }
    }, [scrollableParent]);
    useEffect(() => {
      if (!resolvedItemsElement) {
        return;
      }
      function syncStateFromDataManager() {
        setRequestCount(infiniteScrollInstance.dataManager.getRequestCount());
        setHasPreviousPage(infiniteScrollInstance.dataManager.hasPrevious());
        setHasNextPage(infiniteScrollInstance.dataManager.hasNext());
      }
      const infiniteScrollInstance = useInfiniteScroll({
        // Data
        getPropName: () => data,
        inReverseMode: () => callbackPropsRef.current.reverse,
        shouldFetchNext: () => !callbackPropsRef.current.onlyPrevious,
        shouldFetchPrevious: () => !callbackPropsRef.current.onlyNext,
        shouldPreserveUrl: () => callbackPropsRef.current.preserveUrl,
        getReloadOptions: () => callbackPropsRef.current.params,
        // Elements
        getTriggerMargin: () => callbackPropsRef.current.buffer,
        getStartElement: () => resolvedStartElement,
        getEndElement: () => resolvedEndElement,
        getItemsElement: () => resolvedItemsElement,
        getScrollableParent: () => scrollableParent,
        // Callbacks
        onBeforePreviousRequest: () => setLoadingPrevious(true),
        onBeforeNextRequest: () => setLoadingNext(true),
        onCompletePreviousRequest: ({ completed }) => {
          setLoadingPrevious(false);
          if (completed) {
            syncStateFromDataManager();
          }
        },
        onCompleteNextRequest: ({ completed }) => {
          setLoadingNext(false);
          if (completed) {
            syncStateFromDataManager();
          }
        },
        onDataReset: syncStateFromDataManager
      });
      setInfiniteScroll(infiniteScrollInstance);
      const { dataManager: dataManager2, elementManager: elementManager2 } = infiniteScrollInstance;
      syncStateFromDataManager();
      elementManager2.setupObservers();
      elementManager2.processServerLoadedElements(dataManager2.getLastLoadedPage());
      if (autoLoad) {
        elementManager2.enableTriggers();
      }
      return () => {
        infiniteScrollInstance.flush();
        setInfiniteScroll(null);
      };
    }, [data, resolvedItemsElement, resolvedStartElement, resolvedEndElement, scrollableParent]);
    const manualMode = useMemo(
      () => manual || manualAfter > 0 && requestCount >= manualAfter,
      [manual, manualAfter, requestCount]
    );
    const autoLoad = useMemo(() => !manualMode, [manualMode]);
    useEffect(() => {
      autoLoad ? elementManager?.enableTriggers() : elementManager?.disableTriggers();
    }, [autoLoad, onlyNext, onlyPrevious, resolvedStartElement, resolvedEndElement]);
    useEffect(() => {
      const shouldAutoScroll = autoScroll !== void 0 ? autoScroll : reverse;
      if (shouldAutoScroll) {
        scrollToBottom();
      }
    }, [scrollableParent]);
    useImperativeHandle(
      ref,
      () => ({
        fetchNext: dataManager?.fetchNext || (() => {
        }),
        fetchPrevious: dataManager?.fetchPrevious || (() => {
        }),
        hasPrevious: dataManager?.hasPrevious || (() => false),
        hasNext: dataManager?.hasNext || (() => false)
      }),
      [dataManager]
    );
    const headerAutoMode = autoLoad && !onlyNext;
    const footerAutoMode = autoLoad && !onlyPrevious;
    const sharedExposed = {
      loadingPrevious,
      loadingNext,
      hasPrevious: hasPreviousPage,
      hasNext: hasNextPage
    };
    const exposedPrevious = {
      loading: loadingPrevious,
      fetch: dataManager?.fetchPrevious ?? (() => {
      }),
      autoMode: headerAutoMode,
      manualMode: !headerAutoMode,
      hasMore: hasPreviousPage,
      ...sharedExposed
    };
    const exposedNext = {
      loading: loadingNext,
      fetch: dataManager?.fetchNext ?? (() => {
      }),
      autoMode: footerAutoMode,
      manualMode: !footerAutoMode,
      hasMore: hasNextPage,
      ...sharedExposed
    };
    const exposedSlot = {
      loading: loadingPrevious || loadingNext,
      loadingPrevious,
      loadingNext
    };
    const renderElements = [];
    if (!startElement) {
      renderElements.push(
        createElement(
          "div",
          { ref: startElementRef },
          // Render previous slot or fallback to loading indicator
          renderSlot(previous, exposedPrevious, loadingPrevious ? renderSlot(loading, exposedPrevious) : null)
        )
      );
    }
    renderElements.push(
      createElement(
        as,
        { ...props, ref: itemsElementRef },
        typeof children === "function" ? children(exposedSlot) : children
      )
    );
    if (!endElement) {
      renderElements.push(
        createElement(
          "div",
          { ref: endElementRef },
          // Render next slot or fallback to loading indicator
          renderSlot(next, exposedNext, loadingNext ? renderSlot(loading, exposedNext) : null)
        )
      );
    }
    return createElement(React.Fragment, {}, ...reverse ? [...renderElements].reverse() : renderElements);
  }
);
InfiniteScroll.displayName = "InertiaInfiniteScroll";
var noop2 = () => void 0;
var Link = forwardRef(
  ({
    children,
    as = "a",
    data = {},
    href = "",
    method = "get",
    preserveScroll = false,
    preserveState = null,
    preserveUrl = false,
    replace = false,
    only = [],
    except = [],
    headers = {},
    queryStringArrayFormat = "brackets",
    async = false,
    onClick = noop2,
    onCancelToken = noop2,
    onBefore = noop2,
    onStart = noop2,
    onProgress = noop2,
    onFinish = noop2,
    onCancel = noop2,
    onSuccess = noop2,
    onError = noop2,
    onPrefetching = noop2,
    onPrefetched = noop2,
    prefetch = false,
    cacheFor = 0,
    cacheTags = [],
    viewTransition = false,
    component = null,
    instant = false,
    pageProps = null,
    ...props
  }, ref) => {
    const [inFlightCount, setInFlightCount] = useState(0);
    const hoverTimeout = useRef(void 0);
    const _method = useMemo(() => {
      return isUrlMethodPair(href) ? href.method : method.toLowerCase();
    }, [href, method]);
    const resolvedComponent = useMemo(() => {
      if (component) {
        return component;
      }
      if (instant && isUrlMethodPair(href)) {
        return resolveUrlMethodPairComponent(href);
      }
      return null;
    }, [component, instant, href]);
    const _as = useMemo(() => {
      if (typeof as !== "string" || as.toLowerCase() !== "a") {
        return as;
      }
      return _method !== "get" ? "button" : as.toLowerCase();
    }, [as, _method]);
    const mergeDataArray = useMemo(
      () => mergeDataIntoQueryString(_method, isUrlMethodPair(href) ? href.url : href, data, queryStringArrayFormat),
      [href, _method, data, queryStringArrayFormat]
    );
    const url = useMemo(() => mergeDataArray[0], [mergeDataArray]);
    const _data = useMemo(() => mergeDataArray[1], [mergeDataArray]);
    const baseParams = useMemo(
      () => ({
        data: _data,
        method: _method,
        preserveScroll,
        preserveState: preserveState ?? _method !== "get",
        preserveUrl,
        replace,
        only,
        except,
        headers,
        async,
        component: resolvedComponent,
        pageProps
      }),
      [
        _data,
        _method,
        preserveScroll,
        preserveState,
        preserveUrl,
        replace,
        only,
        except,
        headers,
        async,
        resolvedComponent,
        pageProps
      ]
    );
    const visitParams = useMemo(
      () => ({
        ...baseParams,
        viewTransition,
        onCancelToken,
        onBefore,
        onStart(visit) {
          setInFlightCount((count) => count + 1);
          onStart(visit);
        },
        onProgress,
        onFinish(visit) {
          setInFlightCount((count) => count - 1);
          onFinish(visit);
        },
        onCancel,
        onSuccess,
        onError
      }),
      [
        baseParams,
        viewTransition,
        onCancelToken,
        onBefore,
        onStart,
        onProgress,
        onFinish,
        onCancel,
        onSuccess,
        onError
      ]
    );
    const prefetchModes = useMemo(
      () => {
        if (prefetch === true) {
          return ["hover"];
        }
        if (prefetch === false) {
          return [];
        }
        if (Array.isArray(prefetch)) {
          return prefetch;
        }
        return [prefetch];
      },
      Array.isArray(prefetch) ? prefetch : [prefetch]
    );
    const cacheForValue = useMemo(() => {
      if (cacheFor !== 0) {
        return cacheFor;
      }
      if (prefetchModes.length === 1 && prefetchModes[0] === "click") {
        return 0;
      }
      return config.get("prefetch.cacheFor");
    }, [cacheFor, prefetchModes]);
    const doPrefetch = useMemo(() => {
      return () => {
        router.prefetch(
          url,
          {
            ...baseParams,
            onPrefetching,
            onPrefetched
          },
          { cacheFor: cacheForValue, cacheTags }
        );
      };
    }, [url, baseParams, onPrefetching, onPrefetched, cacheForValue, cacheTags]);
    useEffect(() => {
      return () => {
        clearTimeout(hoverTimeout.current);
      };
    }, []);
    useEffect(() => {
      if (prefetchModes.includes("mount")) {
        setTimeout(() => doPrefetch());
      }
    }, prefetchModes);
    const regularEvents = {
      onClick: (event) => {
        onClick(event);
        if (shouldIntercept(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      }
    };
    const prefetchHoverEvents = {
      onMouseEnter: () => {
        hoverTimeout.current = window.setTimeout(() => {
          doPrefetch();
        }, config.get("prefetch.hoverDelay"));
      },
      onMouseLeave: () => {
        clearTimeout(hoverTimeout.current);
      },
      onClick: regularEvents.onClick
    };
    const prefetchClickEvents = {
      onMouseDown: (event) => {
        if (shouldIntercept(event)) {
          event.preventDefault();
          doPrefetch();
        }
      },
      onKeyDown: (event) => {
        if (shouldNavigate(event)) {
          event.preventDefault();
          doPrefetch();
        }
      },
      onMouseUp: (event) => {
        if (shouldIntercept(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      },
      onKeyUp: (event) => {
        if (shouldNavigate(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      },
      onClick: (event) => {
        onClick(event);
        if (shouldIntercept(event)) {
          event.preventDefault();
        }
      }
    };
    const elProps = useMemo(() => {
      if (_as === "button") {
        return { type: "button" };
      }
      if (_as === "a" || typeof _as !== "string") {
        return { href: url };
      }
      return {};
    }, [_as, url]);
    return createElement(
      _as,
      {
        ...props,
        ...elProps,
        ref,
        ...(() => {
          if (prefetchModes.includes("hover")) {
            return prefetchHoverEvents;
          }
          if (prefetchModes.includes("click")) {
            return prefetchClickEvents;
          }
          return regularEvents;
        })(),
        "data-loading": inFlightCount > 0 ? "" : void 0
      },
      children
    );
  }
);
Link.displayName = "InertiaLink";
var Link_default = Link;
var config = config$1.extend();
function Turnstile({ siteKey, onToken }) {
  const container = useRef(null);
  useEffect(() => {
    let active = true;
    const render = () => {
      if (active && container.current && window.turnstile) {
        window.turnstile.render(container.current, { sitekey: siteKey, callback: onToken, "expired-callback": () => onToken("") });
      }
    };
    const existing = document.querySelector("script[data-digify-turnstile]");
    if (existing) {
      if (window.turnstile) render();
      else existing.addEventListener("load", render, { once: true });
    } else {
      const script = document.createElement("script");
      script.src = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
      script.async = true;
      script.defer = true;
      script.dataset.digifyTurnstile = "true";
      script.addEventListener("load", render, { once: true });
      document.head.appendChild(script);
    }
    return () => {
      active = false;
    };
  }, [onToken, siteKey]);
  return /* @__PURE__ */ jsx("div", { ref: container });
}
function DynamicForm({ form }) {
  const initialValues = useMemo(() => Object.fromEntries(form.fields.filter((field) => !["heading", "paragraph"].includes(field.type)).map((field) => [field.key, field.type === "multiselect" ? [] : field.type === "checkbox" ? false : ""])), [form.fields]);
  const { flash } = usePage().props;
  const { data, setData, post, processing, errors, reset } = useForm({ ...initialValues, _website: "", captcha_token: "" });
  const isVisible = (field) => {
    const logic = field.conditional_logic;
    if (!logic?.field) return true;
    const actual = data[logic.field];
    if (logic.operator === "not_equals") return actual !== logic.value;
    if (logic.operator === "contains") return Array.isArray(actual) ? actual.includes(logic.value ?? "") : String(actual).includes(logic.value ?? "");
    return actual === logic.value;
  };
  const width = (value) => ({ full: "md:col-span-6", half: "md:col-span-3", third: "md:col-span-2", two_thirds: "md:col-span-4" })[value];
  const fieldClass = "w-full rounded-xl border border-brand-line bg-white px-4 py-3 outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20";
  return /* @__PURE__ */ jsxs("form", { onSubmit: (event) => {
    event.preventDefault();
    post(form.action, { preserveScroll: true, onSuccess: () => {
      reset();
      if (form.redirect_url) window.location.assign(form.redirect_url);
    } });
  }, className: "grid gap-5 md:grid-cols-6", children: [
    /* @__PURE__ */ jsx("input", { type: "text", name: "_website", value: String(data._website), onChange: (event) => setData("_website", event.target.value), tabIndex: -1, autoComplete: "off", className: "sr-only", "aria-hidden": "true" }),
    form.fields.map((field) => {
      if (!isVisible(field)) return null;
      if (field.type === "heading") return /* @__PURE__ */ jsx("h3", { className: "md:col-span-6 text-2xl", children: field.label }, field.key);
      if (field.type === "paragraph") return /* @__PURE__ */ jsx("p", { className: "md:col-span-6 text-brand-muted", children: field.help_text || field.label }, field.key);
      const required = field.rules.includes("required");
      const error = errors[field.key];
      return /* @__PURE__ */ jsxs("label", { className: `${width(field.width)} space-y-2`, children: [
        /* @__PURE__ */ jsxs("span", { className: "block text-sm font-semibold text-brand-navy", children: [
          field.label,
          required && /* @__PURE__ */ jsx("span", { className: "text-red-600", children: " *" })
        ] }),
        field.type === "textarea" ? /* @__PURE__ */ jsx("textarea", { value: String(data[field.key] ?? ""), onChange: (event) => setData(field.key, event.target.value), placeholder: field.placeholder, className: fieldClass, rows: 5 }) : field.type === "select" ? /* @__PURE__ */ jsxs("select", { value: String(data[field.key] ?? ""), onChange: (event) => setData(field.key, event.target.value), className: fieldClass, children: [
          /* @__PURE__ */ jsx("option", { value: "", children: "—" }),
          field.options.map((option) => /* @__PURE__ */ jsx("option", { value: option.value, children: option.label }, option.value))
        ] }) : field.type === "multiselect" ? /* @__PURE__ */ jsx("select", { multiple: true, value: Array.isArray(data[field.key]) ? data[field.key] : [], onChange: (event) => setData(field.key, Array.from(event.target.selectedOptions).map((option) => option.value)), className: fieldClass, children: field.options.map((option) => /* @__PURE__ */ jsx("option", { value: option.value, children: option.label }, option.value)) }) : field.type === "radio" ? /* @__PURE__ */ jsx("span", { className: "flex flex-wrap gap-4", children: field.options.map((option) => /* @__PURE__ */ jsxs("label", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ jsx("input", { type: "radio", name: field.key, value: option.value, checked: data[field.key] === option.value, onChange: () => setData(field.key, option.value) }),
          " ",
          option.label
        ] }, option.value)) }) : field.type === "checkbox" ? /* @__PURE__ */ jsx("input", { type: "checkbox", checked: Boolean(data[field.key]), onChange: (event) => setData(field.key, event.target.checked), className: "size-5 rounded border-brand-line" }) : field.type === "file" ? /* @__PURE__ */ jsx("input", { type: "file", onChange: (event) => setData(field.key, event.target.files?.[0] ?? null), className: fieldClass }) : /* @__PURE__ */ jsx("input", { type: field.type, value: String(data[field.key] ?? ""), onChange: (event) => setData(field.key, event.target.value), placeholder: field.placeholder, className: fieldClass }),
        field.help_text && /* @__PURE__ */ jsx("span", { className: "block text-xs text-brand-muted", children: field.help_text }),
        error && /* @__PURE__ */ jsx("span", { className: "block text-sm text-red-600", children: error })
      ] }, field.key);
    }),
    form.captcha_enabled && form.captcha_site_key && /* @__PURE__ */ jsxs("div", { className: "md:col-span-6", children: [
      /* @__PURE__ */ jsx(Turnstile, { siteKey: form.captcha_site_key, onToken: (token) => setData("captcha_token", token) }),
      errors.captcha_token && /* @__PURE__ */ jsx("p", { className: "text-sm text-red-600", children: errors.captcha_token })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "md:col-span-6", children: /* @__PURE__ */ jsx("button", { type: "submit", disabled: processing, className: "rounded-full bg-brand-navy px-7 py-3 font-semibold text-white disabled:opacity-50", children: processing ? "…" : form.submit_label }) }),
    flash.form_success && /* @__PURE__ */ jsx("p", { role: "status", className: "md:col-span-6 rounded-xl bg-emerald-50 p-4 text-emerald-800", children: flash.form_success })
  ] });
}
const applicationSchema = z.object({
  first_name: z.string().min(1, "First name is required."),
  last_name: z.string().min(1, "Last name is required."),
  email: z.string().email("Enter a valid email address."),
  phone: z.string().optional(),
  cover_letter: z.string().max(2e3, "Cover letter must be 2000 characters or fewer.").optional(),
  portfolio_url: z.string().url("Enter a valid URL.").optional().or(z.literal("")),
  linkedin_url: z.string().url("Enter a valid URL.").optional().or(z.literal("")),
  cv: z.custom((value) => typeof FileList !== "undefined" && value instanceof FileList && value.length === 1, "A CV is required.").refine((files) => files.item(0)?.type === "application/pdf" || files.item(0)?.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "CV must be a PDF or DOCX file.").refine((files) => (files.item(0)?.size ?? 0) <= 10 * 1024 * 1024, "CV must be 10 MB or smaller."),
  website: z.string().optional()
});
function Apply() {
  const { job, dynamicForm } = usePage().props;
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm$1({
    resolver: zodResolver(applicationSchema)
  });
  const submit = (values) => {
    const data = new FormData();
    Object.entries(values).forEach(([key, value]) => {
      if (key === "cv" && value instanceof FileList) {
        data.append("cv", value.item(0));
      } else if (typeof value === "string") {
        data.append(key, value);
      }
    });
    router.post(window.location.pathname.replace(/\/$/, ""), data, {
      forceFormData: true
    });
  };
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Apply - " + job.title }),
    /* @__PURE__ */ jsxs("article", { className: "mx-auto max-w-2xl space-y-8", children: [
      /* @__PURE__ */ jsxs("header", { className: "space-y-3", children: [
        /* @__PURE__ */ jsxs(Link_default, { href: "/careers/" + job.slug + "/", className: "text-sm text-slate-500", children: [
          "← ",
          job.title
        ] }),
        /* @__PURE__ */ jsx("h1", { className: "text-4xl font-semibold text-brand-navy", children: "Apply for this role" }),
        /* @__PURE__ */ jsx("p", { className: "text-slate-600", children: job.department.name })
      ] }),
      dynamicForm ? /* @__PURE__ */ jsx(DynamicForm, { form: dynamicForm }) : /* @__PURE__ */ jsxs("form", { onSubmit: handleSubmit(submit), className: "space-y-6", encType: "multipart/form-data", children: [
        /* @__PURE__ */ jsxs("div", { className: "grid gap-5 md:grid-cols-2", children: [
          /* @__PURE__ */ jsx(Field, { label: "First name", error: errors.first_name?.message, children: /* @__PURE__ */ jsx("input", { ...register("first_name") }) }),
          /* @__PURE__ */ jsx(Field, { label: "Last name", error: errors.last_name?.message, children: /* @__PURE__ */ jsx("input", { ...register("last_name") }) })
        ] }),
        /* @__PURE__ */ jsx(Field, { label: "Email", error: errors.email?.message, children: /* @__PURE__ */ jsx("input", { type: "email", ...register("email") }) }),
        /* @__PURE__ */ jsx(Field, { label: "Phone", error: errors.phone?.message, children: /* @__PURE__ */ jsx("input", { ...register("phone") }) }),
        /* @__PURE__ */ jsx(Field, { label: "Cover letter", error: errors.cover_letter?.message, children: /* @__PURE__ */ jsx("textarea", { rows: 6, ...register("cover_letter") }) }),
        /* @__PURE__ */ jsx(Field, { label: "Portfolio URL", error: errors.portfolio_url?.message, children: /* @__PURE__ */ jsx("input", { type: "url", ...register("portfolio_url") }) }),
        /* @__PURE__ */ jsx(Field, { label: "LinkedIn URL", error: errors.linkedin_url?.message, children: /* @__PURE__ */ jsx("input", { type: "url", ...register("linkedin_url") }) }),
        /* @__PURE__ */ jsx(Field, { label: "CV (PDF or DOCX, max 10 MB)", error: errors.cv?.message, children: /* @__PURE__ */ jsx("input", { type: "file", accept: ".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document", ...register("cv") }) }),
        /* @__PURE__ */ jsx("div", { className: "absolute -start-[10000px] h-px w-px overflow-hidden", "aria-hidden": "true", children: /* @__PURE__ */ jsxs("label", { children: [
          "Website",
          /* @__PURE__ */ jsx("input", { tabIndex: -1, autoComplete: "off", ...register("website") })
        ] }) }),
        /* @__PURE__ */ jsx("button", { disabled: isSubmitting, className: "rounded-md bg-brand-navy px-5 py-3 font-medium text-white disabled:opacity-50", type: "submit", children: "Submit application" })
      ] })
    ] })
  ] });
}
function Field({ label, error, children }) {
  return /* @__PURE__ */ jsxs("label", { className: "block space-y-2 text-sm font-medium text-slate-700", children: [
    /* @__PURE__ */ jsx("span", { children: label }),
    children,
    error && /* @__PURE__ */ jsx("span", { className: "block font-normal text-red-600", children: error })
  ] });
}
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Apply
}, Symbol.toStringTag, { value: "Module" }));
function Index() {
  const { jobs, filters } = usePage().props;
  const groups = jobs.reduce((result, job) => {
    const key = job.department.name;
    result[key] ??= { sort_order: job.department.sort_order, jobs: [] };
    result[key].jobs.push(job);
    return result;
  }, {});
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Careers" }),
    /* @__PURE__ */ jsxs("div", { className: "space-y-12", children: [
      /* @__PURE__ */ jsxs("header", { className: "max-w-2xl space-y-4", children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm font-medium uppercase tracking-wide text-slate-500", children: "Careers" }),
        /* @__PURE__ */ jsx("h1", { className: "text-4xl font-semibold tracking-tight text-brand-navy", children: "Find your next opportunity" }),
        /* @__PURE__ */ jsx("p", { className: "text-lg leading-8 text-slate-600", children: "Join a team building thoughtful digital experiences." })
      ] }),
      /* @__PURE__ */ jsxs("form", { method: "get", action: typeof window === "undefined" ? "/careers/" : window.location.pathname, className: "grid gap-4 border-y border-slate-200 py-6 md:grid-cols-4", children: [
        /* @__PURE__ */ jsxs("label", { className: "space-y-2 text-sm", children: [
          /* @__PURE__ */ jsx("span", { className: "font-medium", children: "Employment type" }),
          /* @__PURE__ */ jsxs("select", { name: "employment_type", defaultValue: filters.employment_type ?? "", className: "w-full rounded-md border border-slate-300 p-2", children: [
            /* @__PURE__ */ jsx("option", { value: "", children: "All" }),
            /* @__PURE__ */ jsx("option", { value: "full_time", children: "Full time" }),
            /* @__PURE__ */ jsx("option", { value: "part_time", children: "Part time" }),
            /* @__PURE__ */ jsx("option", { value: "contract", children: "Contract" }),
            /* @__PURE__ */ jsx("option", { value: "internship", children: "Internship" }),
            /* @__PURE__ */ jsx("option", { value: "temporary", children: "Temporary" })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("label", { className: "space-y-2 text-sm", children: [
          /* @__PURE__ */ jsx("span", { className: "font-medium", children: "Workplace" }),
          /* @__PURE__ */ jsxs("select", { name: "workplace_type", defaultValue: filters.workplace_type ?? "", className: "w-full rounded-md border border-slate-300 p-2", children: [
            /* @__PURE__ */ jsx("option", { value: "", children: "All" }),
            /* @__PURE__ */ jsx("option", { value: "on_site", children: "On site" }),
            /* @__PURE__ */ jsx("option", { value: "hybrid", children: "Hybrid" }),
            /* @__PURE__ */ jsx("option", { value: "remote", children: "Remote" })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("label", { className: "space-y-2 text-sm", children: [
          /* @__PURE__ */ jsx("span", { className: "font-medium", children: "Department" }),
          /* @__PURE__ */ jsx("input", { name: "department", defaultValue: filters.department ?? "", className: "w-full rounded-md border border-slate-300 p-2" })
        ] }),
        /* @__PURE__ */ jsx("button", { type: "submit", className: "self-end rounded-md bg-brand-navy px-4 py-2 font-medium text-white", children: "Filter" })
      ] }),
      Object.keys(groups).length === 0 ? /* @__PURE__ */ jsxs("div", { className: "rounded-lg border border-slate-200 p-8 text-center", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-2xl font-semibold text-brand-navy", children: "No open vacancies" }),
        /* @__PURE__ */ jsx("p", { className: "mt-2 text-slate-600", children: "Send us a general application and tell us how you could contribute." })
      ] }) : /* @__PURE__ */ jsx("div", { className: "space-y-12", children: Object.entries(groups).sort(([, a], [, b]) => a.sort_order - b.sort_order).map(([department, group]) => /* @__PURE__ */ jsxs("section", { className: "space-y-5", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-2xl font-semibold text-brand-navy", children: department }),
        /* @__PURE__ */ jsx("div", { className: "grid gap-5 md:grid-cols-2", children: group.jobs.map((job) => /* @__PURE__ */ jsxs(Link_default, { href: "/careers/" + job.slug + "/", className: "rounded-lg border border-slate-200 p-6 transition hover:border-brand-navy", children: [
          /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-brand-navy", children: job.title }),
          /* @__PURE__ */ jsxs("p", { className: "mt-2 text-sm text-slate-500", children: [
            job.employment_type,
            " · ",
            job.workplace_type,
            job.city ? " · " + job.city : ""
          ] }),
          /* @__PURE__ */ jsxs("p", { className: "mt-4 text-sm text-slate-600", children: [
            "Posted ",
            job.relative_published_at
          ] })
        ] }, job.id)) })
      ] }, department)) })
    ] })
  ] });
}
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index
}, Symbol.toStringTag, { value: "Module" }));
function Show$3() {
  const { job } = usePage().props;
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsxs(Head_default, { title: job.meta.title, children: [
      /* @__PURE__ */ jsx("meta", { name: "description", content: job.meta.description }),
      /* @__PURE__ */ jsx("link", { rel: "canonical", href: job.meta.canonical }),
      Object.entries(job.meta.hreflang).map(([locale, href]) => /* @__PURE__ */ jsx("link", { rel: "alternate", hrefLang: locale, href }, locale)),
      /* @__PURE__ */ jsx("script", { type: "application/ld+json", children: JSON.stringify(job.json_ld) })
    ] }),
    /* @__PURE__ */ jsxs("article", { className: "mx-auto max-w-3xl space-y-10", children: [
      /* @__PURE__ */ jsxs("header", { className: "space-y-5", children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm font-medium uppercase tracking-wide text-slate-500", children: job.department.name }),
        /* @__PURE__ */ jsx("h1", { className: "text-4xl font-semibold tracking-tight text-brand-navy", children: job.title }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600", children: [
          /* @__PURE__ */ jsx("span", { children: job.employment_type }),
          /* @__PURE__ */ jsx("span", { children: job.workplace_type }),
          job.city && /* @__PURE__ */ jsx("span", { children: job.city }),
          /* @__PURE__ */ jsxs("span", { children: [
            "Posted ",
            job.relative_published_at
          ] })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "space-y-8 leading-8 text-slate-700", children: [
        /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsx("h2", { className: "mb-3 text-2xl font-semibold text-brand-navy", children: "Description" }),
          /* @__PURE__ */ jsx("p", { children: job.description })
        ] }),
        /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsx("h2", { className: "mb-3 text-2xl font-semibold text-brand-navy", children: "Responsibilities" }),
          /* @__PURE__ */ jsx("p", { children: job.responsibilities })
        ] }),
        /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsx("h2", { className: "mb-3 text-2xl font-semibold text-brand-navy", children: "Requirements" }),
          /* @__PURE__ */ jsx("p", { children: job.requirements })
        ] }),
        /* @__PURE__ */ jsxs("section", { children: [
          /* @__PURE__ */ jsx("h2", { className: "mb-3 text-2xl font-semibold text-brand-navy", children: "Benefits" }),
          /* @__PURE__ */ jsx("p", { children: job.benefits })
        ] })
      ] }),
      job.salary_is_public && /* @__PURE__ */ jsxs("p", { className: "text-lg font-medium text-brand-navy", children: [
        "Salary: ",
        job.salary_min,
        "–",
        job.salary_max,
        " ",
        job.salary_currency,
        " / ",
        job.salary_period
      ] }),
      /* @__PURE__ */ jsx(Link_default, { href: "/careers/" + job.slug + "/apply/", className: "inline-flex rounded-md bg-brand-navy px-5 py-3 font-medium text-white", children: "Apply" })
    ] })
  ] });
}
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show$3
}, Symbol.toStringTag, { value: "Module" }));
function ThankYou() {
  const { locale, referenceCode } = usePage().props;
  const arabic = locale === "ar";
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: arabic ? "شكرًا لتقديمك" : "Thank you" }),
    /* @__PURE__ */ jsxs("section", { className: "mx-auto max-w-2xl space-y-5 text-center", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-4xl font-semibold text-brand-navy", children: arabic ? "شكرًا لتقديمك" : "Thank you for applying" }),
      /* @__PURE__ */ jsx("p", { className: "text-lg text-slate-600", children: arabic ? "تم استلام طلبك. تم إرسال رسالة تأكيد بالبريد الإلكتروني." : "Your application has been received. A confirmation email has been sent." }),
      referenceCode && /* @__PURE__ */ jsxs("p", { className: "font-medium text-brand-navy", children: [
        arabic ? "المرجع: " : "Reference: ",
        referenceCode
      ] }),
      /* @__PURE__ */ jsx(Link_default, { href: arabic ? "/ar/careers/" : "/careers/", className: "inline-flex rounded-md bg-brand-navy px-5 py-3 font-medium text-white", children: arabic ? "العودة إلى الوظائف" : "Back to careers" })
    ] })
  ] });
}
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ThankYou
}, Symbol.toStringTag, { value: "Module" }));
function Standalone() {
  const { form } = usePage().props;
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: form.name }),
    /* @__PURE__ */ jsxs("section", { className: "mx-auto max-w-3xl", children: [
      /* @__PURE__ */ jsxs("header", { className: "mb-10", children: [
        /* @__PURE__ */ jsx("h1", { className: "text-5xl", children: form.name }),
        form.description && /* @__PURE__ */ jsx("p", { className: "mt-4 text-lg text-brand-muted", children: form.description })
      ] }),
      /* @__PURE__ */ jsx(DynamicForm, { form })
    ] })
  ] });
}
const __vite_glob_0_4 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Standalone
}, Symbol.toStringTag, { value: "Module" }));
function text(props, key) {
  return typeof props[key] === "string" ? props[key] : "";
}
function records(props, key) {
  return Array.isArray(props[key]) ? props[key] : [];
}
function nestedRecords(props, key) {
  return records(props, key).map((item) => {
    const data = item.data;
    return typeof data === "object" && data !== null ? data : item;
  });
}
function mediaUrl(props) {
  const media = props.media;
  if (typeof media === "object" && media !== null && "url" in media && typeof media.url === "string") {
    return media.url;
  }
  return text(props, "media_url");
}
function CapabilityScroll({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "flex snap-x gap-5 overflow-x-auto pb-4", children: nestedRecords(props, "items").map((item, index) => /* @__PURE__ */ jsxs("article", { className: "min-w-[280px] snap-start rounded-3xl border border-brand-line bg-white p-7 sm:min-w-[360px]", children: [
      /* @__PURE__ */ jsx("span", { className: "text-2xl text-brand-blue", children: String(item.icon ?? "✦") }),
      /* @__PURE__ */ jsx("h3", { className: "mt-6 text-2xl", children: String(item.title ?? "") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 text-brand-muted", children: String(item.body ?? "") })
    ] }, index)) })
  ] });
}
function CaseReel({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "grid gap-6 md:grid-cols-2", children: records(props, "projects").map((project) => /* @__PURE__ */ jsxs("a", { href: `/projects/${String(project.slug)}/`, className: "group rounded-3xl border border-brand-line bg-white p-7 transition hover:-translate-y-1 hover:shadow-xl", children: [
      /* @__PURE__ */ jsx("p", { className: "text-sm text-brand-muted", children: String(project.client_name ?? "") }),
      /* @__PURE__ */ jsx("h3", { className: "mt-3 text-2xl", children: String(project.title ?? "") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 text-brand-muted", children: String(project.summary ?? "") })
    ] }, String(project.id))) })
  ] });
}
function CharacterLoop({ props }) {
  const media = mediaUrl(props);
  return /* @__PURE__ */ jsxs("section", { className: "grid items-center gap-8 rounded-[2rem] bg-brand-yellow p-8 md:grid-cols-2 md:p-12", children: [
    /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
      /* @__PURE__ */ jsx("p", { className: "mt-4 text-brand-navy/75", children: text(props, "body") })
    ] }),
    media && /* @__PURE__ */ jsx("video", { src: media, "aria-label": text(props, "alt"), className: "w-full rounded-3xl", autoPlay: true, muted: true, loop: true, playsInline: true })
  ] });
}
function CtaBand({ props }) {
  const theme = text(props, "theme");
  const colors = theme === "coral" ? "bg-brand-orange text-brand-navy" : theme === "white" ? "bg-white text-brand-navy border border-brand-line" : "bg-brand-navy text-white";
  return /* @__PURE__ */ jsxs("section", { className: `flex flex-col gap-8 rounded-[2rem] p-8 md:flex-row md:items-center md:justify-between md:p-12 ${colors}`, children: [
    /* @__PURE__ */ jsxs("div", { children: [
      /* @__PURE__ */ jsx("h2", { className: `text-4xl ${theme === "navy" || !theme ? "text-white" : ""}`, children: text(props, "title") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 max-w-2xl opacity-75", children: text(props, "body") })
    ] }),
    /* @__PURE__ */ jsx("a", { href: text(props, "cta_url"), className: "shrink-0 rounded-full bg-brand-yellow px-6 py-3 font-semibold text-brand-navy", children: text(props, "cta_label") })
  ] });
}
function Faq({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "mx-auto max-w-3xl space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "divide-y divide-brand-line border-y border-brand-line", children: nestedRecords(props, "items").map((item, index) => /* @__PURE__ */ jsxs("details", { className: "group py-5", children: [
      /* @__PURE__ */ jsx("summary", { className: "cursor-pointer list-none font-semibold text-brand-navy", children: String(item.question ?? "") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 text-brand-muted", children: String(item.answer ?? "") })
    ] }, index)) })
  ] });
}
function FormBlock({ props }) {
  const form = typeof props.form === "object" && props.form !== null ? props.form : null;
  if (!form) return null;
  return /* @__PURE__ */ jsxs("section", { className: "rounded-[2rem] bg-brand-paper p-8 md:p-12", children: [
    /* @__PURE__ */ jsxs("div", { className: "mb-8 max-w-2xl", children: [
      /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") || form.name }),
      form.description && /* @__PURE__ */ jsx("p", { className: "mt-3 text-brand-muted", children: form.description })
    ] }),
    /* @__PURE__ */ jsx(DynamicForm, { form })
  ] });
}
function HeroCinematic({ props }) {
  const image = mediaUrl(props);
  return /* @__PURE__ */ jsxs("section", { className: "relative isolate min-h-[70vh] overflow-hidden rounded-[2rem] bg-brand-navy px-6 py-24 text-white sm:px-12", children: [
    image && /* @__PURE__ */ jsx("img", { src: image, alt: text(props, "alt"), className: "absolute inset-0 -z-20 h-full w-full object-cover" }),
    props.dark_overlay !== false && /* @__PURE__ */ jsx("div", { className: "absolute inset-0 -z-10 bg-brand-navy/70" }),
    /* @__PURE__ */ jsxs("div", { className: "max-w-3xl space-y-6", children: [
      text(props, "eyebrow") && /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold uppercase tracking-[0.2em] text-brand-yellow", children: text(props, "eyebrow") }),
      /* @__PURE__ */ jsx("h1", { className: "text-5xl font-bold tracking-tight text-white md:text-7xl", children: text(props, "title") }),
      text(props, "body") && /* @__PURE__ */ jsx("p", { className: "max-w-2xl text-lg text-white/80", children: text(props, "body") }),
      text(props, "cta_url") && /* @__PURE__ */ jsx("a", { href: text(props, "cta_url"), className: "inline-flex rounded-full bg-brand-yellow px-6 py-3 font-semibold text-brand-navy", children: text(props, "cta_label") || "Learn more" })
    ] })
  ] });
}
function HeroInterior({ props }) {
  const image = mediaUrl(props);
  return /* @__PURE__ */ jsxs("section", { className: "grid items-center gap-10 rounded-[2rem] bg-brand-paper p-8 md:grid-cols-2 md:p-12", children: [
    /* @__PURE__ */ jsxs("div", { className: "space-y-5", children: [
      /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold uppercase tracking-[0.2em] text-brand-blue", children: text(props, "eyebrow") }),
      /* @__PURE__ */ jsx("h1", { className: "text-5xl font-bold", children: text(props, "title") }),
      /* @__PURE__ */ jsx("p", { className: "text-lg text-brand-muted", children: text(props, "body") })
    ] }),
    image && /* @__PURE__ */ jsx("img", { src: image, alt: text(props, "alt"), className: "aspect-[4/3] w-full rounded-3xl object-cover" })
  ] });
}
function JobsList({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "divide-y divide-brand-line rounded-3xl border border-brand-line bg-white", children: records(props, "jobs").map((job) => /* @__PURE__ */ jsxs("a", { href: `/careers/${String(job.slug)}/`, className: "flex flex-col gap-3 p-6 transition hover:bg-brand-paper sm:flex-row sm:items-center sm:justify-between", children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx("h3", { className: "text-xl", children: String(job.title ?? "") }),
        /* @__PURE__ */ jsxs("p", { className: "text-sm text-brand-muted", children: [
          String(job.department ?? ""),
          " · ",
          String(job.workplace_type ?? "")
        ] })
      ] }),
      /* @__PURE__ */ jsx("span", { className: "font-semibold text-brand-blue", children: "View role →" })
    ] }, String(job.id))) })
  ] });
}
function LogoMarquee({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8 overflow-hidden", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-center text-3xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "flex flex-wrap items-center justify-center gap-10", children: records(props, "media").map((item) => /* @__PURE__ */ jsx("img", { src: String(item.url), alt: String(item.name ?? ""), className: "max-h-12 max-w-40 object-contain grayscale" }, String(item.id))) })
  ] });
}
function MediaFull({ props }) {
  const url = mediaUrl(props);
  if (!url) return null;
  return /* @__PURE__ */ jsxs("figure", { className: "space-y-3", children: [
    /* @__PURE__ */ jsx("img", { src: url, alt: text(props, "alt"), className: "max-h-[80vh] w-full rounded-[2rem] object-cover" }),
    /* @__PURE__ */ jsx("figcaption", { className: "text-sm text-brand-muted", children: text(props, "caption") })
  ] });
}
function PostsGrid({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "grid gap-5 md:grid-cols-3", children: records(props, "posts").map((post) => /* @__PURE__ */ jsxs("a", { href: `/insights/${String(post.slug)}/`, className: "rounded-3xl border border-brand-line bg-white p-6", children: [
      /* @__PURE__ */ jsx("span", { className: "text-xs font-semibold uppercase tracking-wide text-brand-blue", children: String(post.category ?? "") }),
      /* @__PURE__ */ jsx("h3", { className: "mt-4 text-xl", children: String(post.title ?? "") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 text-sm text-brand-muted", children: String(post.excerpt ?? "") })
    ] }, String(post.id))) })
  ] });
}
function ProcessTriptych({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "grid gap-5 md:grid-cols-3", children: nestedRecords(props, "items").map((item, index) => /* @__PURE__ */ jsxs("article", { className: "rounded-3xl bg-brand-navy p-7 text-white", children: [
      /* @__PURE__ */ jsxs("span", { className: "text-brand-yellow", children: [
        "0",
        index + 1
      ] }),
      /* @__PURE__ */ jsx("h3", { className: "mt-8 text-2xl text-white", children: String(item.title ?? "") }),
      /* @__PURE__ */ jsx("p", { className: "mt-3 text-white/70", children: String(item.body ?? "") })
    ] }, index)) })
  ] });
}
function RichText({ props }) {
  return /* @__PURE__ */ jsx("section", { className: "prose prose-lg mx-auto max-w-3xl text-brand-text", dangerouslySetInnerHTML: { __html: text(props, "content") } });
}
function StatRow({ props }) {
  return /* @__PURE__ */ jsx("section", { className: "grid gap-px overflow-hidden rounded-3xl bg-brand-line sm:grid-cols-2 lg:grid-cols-4", children: nestedRecords(props, "items").map((item, index) => /* @__PURE__ */ jsxs("div", { className: "bg-white p-8", children: [
    /* @__PURE__ */ jsx("strong", { className: "block text-4xl text-brand-navy", children: String(item.value ?? "") }),
    /* @__PURE__ */ jsx("span", { className: "mt-2 block text-brand-muted", children: String(item.label ?? "") })
  ] }, `${String(item.value)}-${index}`)) });
}
function Testimonials({ props }) {
  return /* @__PURE__ */ jsxs("section", { className: "space-y-8", children: [
    /* @__PURE__ */ jsx("h2", { className: "text-4xl", children: text(props, "title") }),
    /* @__PURE__ */ jsx("div", { className: "grid gap-5 md:grid-cols-2", children: nestedRecords(props, "items").map((item, index) => /* @__PURE__ */ jsxs("figure", { className: "rounded-3xl bg-brand-paper p-8", children: [
      /* @__PURE__ */ jsxs("blockquote", { className: "text-xl leading-8 text-brand-navy", children: [
        "“",
        String(item.quote ?? ""),
        "”"
      ] }),
      /* @__PURE__ */ jsxs("figcaption", { className: "mt-6 text-sm text-brand-muted", children: [
        /* @__PURE__ */ jsx("strong", { className: "text-brand-navy", children: String(item.author ?? "") }),
        item.role ? ` · ${String(item.role)}` : "",
        item.company ? `, ${String(item.company)}` : ""
      ] })
    ] }, index)) })
  ] });
}
const blockRegistry = {
  hero_cinematic: HeroCinematic,
  hero_interior: HeroInterior,
  case_reel: CaseReel,
  stat_row: StatRow,
  process_triptych: ProcessTriptych,
  capability_scroll: CapabilityScroll,
  logo_marquee: LogoMarquee,
  testimonials: Testimonials,
  character_loop: CharacterLoop,
  posts_grid: PostsGrid,
  jobs_list: JobsList,
  faq: Faq,
  form: FormBlock,
  cta_band: CtaBand,
  rich_text: RichText,
  media_full: MediaFull
};
function BlockRenderer({ blocks }) {
  return /* @__PURE__ */ jsx("div", { className: "space-y-20", children: blocks.map((block) => {
    const Component = blockRegistry[block.type];
    return Component ? /* @__PURE__ */ jsx(Component, { props: block.props }, block.id) : null;
  }) });
}
function Show$2() {
  const { page, blocks } = usePage().props;
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: page.seo?.title || page.title, children: /* @__PURE__ */ jsx("meta", { "head-key": "description", name: "description", content: page.seo?.description || page.excerpt || "" }) }),
    blocks.length > 0 ? /* @__PURE__ */ jsx(BlockRenderer, { blocks }) : /* @__PURE__ */ jsxs("section", { className: "py-20 text-center", children: [
      /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold uppercase tracking-[0.2em] text-brand-blue", children: "Digify" }),
      /* @__PURE__ */ jsx("h1", { className: "mt-4 text-5xl", children: page.title }),
      page.excerpt && /* @__PURE__ */ jsx("p", { className: "mx-auto mt-5 max-w-2xl text-lg text-brand-muted", children: page.excerpt })
    ] })
  ] });
}
const __vite_glob_0_5 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show$2
}, Symbol.toStringTag, { value: "Module" }));
function Show$1() {
  const { post } = usePage().props;
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: post.seo?.title || post.title, children: /* @__PURE__ */ jsx("meta", { "head-key": "description", name: "description", content: post.seo?.description || post.excerpt || "" }) }),
    /* @__PURE__ */ jsxs("article", { className: "mx-auto max-w-3xl", children: [
      /* @__PURE__ */ jsxs("header", { className: "mb-12 space-y-4", children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold uppercase tracking-wide text-brand-blue", children: post.category }),
        /* @__PURE__ */ jsx("h1", { className: "text-5xl", children: post.title }),
        /* @__PURE__ */ jsxs("p", { className: "text-brand-muted", children: [
          post.published_at,
          " · ",
          post.reading_time,
          " min read"
        ] })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "prose prose-lg", dangerouslySetInnerHTML: { __html: post.body } })
    ] })
  ] });
}
const __vite_glob_0_6 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show$1
}, Symbol.toStringTag, { value: "Module" }));
function Show() {
  const { project, blocks } = usePage().props;
  return /* @__PURE__ */ jsxs(Fragment, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: project.title }),
    /* @__PURE__ */ jsxs("header", { className: "mb-16 grid gap-8 border-b border-brand-line pb-12 md:grid-cols-2", children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx("p", { className: "text-sm font-semibold uppercase tracking-wide text-brand-blue", children: project.client_name }),
        /* @__PURE__ */ jsx("h1", { className: "mt-4 text-5xl", children: project.title })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx("p", { className: "text-lg text-brand-muted", children: project.summary }),
        /* @__PURE__ */ jsx("p", { className: "mt-5 text-sm", children: [project.sector, project.discipline, project.year].filter(Boolean).join(" · ") })
      ] })
    ] }),
    /* @__PURE__ */ jsx(BlockRenderer, { blocks })
  ] });
}
const __vite_glob_0_7 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show
}, Symbol.toStringTag, { value: "Module" }));
function AppLayout({ children }) {
  const { locale, direction, locales, settings, menus } = usePage().props;
  const currentLocale = locales.find((item) => item.code === locale);
  const otherLocale = locales.find((item) => item.code !== locale) ?? currentLocale;
  const currentPath = typeof window === "undefined" ? "/" : window.location.pathname;
  const pathParts = currentPath.split("/").filter(Boolean);
  const pathWithoutLocale = ["en", "ar"].includes(pathParts[0] ?? "") ? pathParts.slice(1) : pathParts;
  const languagePrefix = otherLocale?.code === "ar" ? "ar" : "";
  const localizedPath = [languagePrefix, ...pathWithoutLocale].filter(Boolean).join("/");
  const languagePath = localizedPath === "" ? "/" : `/${localizedPath}${currentPath.endsWith("/") ? "/" : ""}`;
  const mainMenu = menus.main?.items ?? [];
  useEffect(() => {
    document.documentElement.lang = locale;
    document.documentElement.dir = direction;
  }, [direction, locale]);
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-white text-slate-800", children: [
    /* @__PURE__ */ jsx("header", { className: "border-b border-slate-200", children: /* @__PURE__ */ jsxs("div", { className: "mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-5", children: [
      /* @__PURE__ */ jsx(Link_default, { href: locale === "ar" ? "/ar/" : "/", className: "text-xl font-semibold tracking-tight text-brand-navy", children: settings.site_name }),
      /* @__PURE__ */ jsxs("nav", { className: "flex items-center gap-6 text-sm font-medium", children: [
        mainMenu.map((item) => /* @__PURE__ */ jsx(Link_default, { href: item.url, target: item.target === "new" ? "_blank" : void 0, className: "text-slate-700 hover:text-brand-navy", children: item.label }, item.id)),
        mainMenu.length === 0 && /* @__PURE__ */ jsx(Link_default, { href: locale === "ar" ? "/ar/careers/" : "/careers/", className: "text-slate-700 hover:text-brand-navy", children: "Careers" }),
        /* @__PURE__ */ jsx("a", { href: languagePath, className: "text-slate-700 hover:text-brand-navy", children: otherLocale?.native_name })
      ] })
    ] }) }),
    /* @__PURE__ */ jsx("main", { className: "mx-auto max-w-6xl px-6 py-16", children }),
    /* @__PURE__ */ jsx("footer", { className: "border-t border-slate-200", children: /* @__PURE__ */ jsx("div", { className: "mx-auto max-w-6xl px-6 py-8 text-sm text-slate-500", children: settings.site_name }) })
  ] });
}
const pages = /* @__PURE__ */ Object.assign({ "./Pages/Careers/Apply.tsx": __vite_glob_0_0, "./Pages/Careers/Index.tsx": __vite_glob_0_1, "./Pages/Careers/Show.tsx": __vite_glob_0_2, "./Pages/Careers/ThankYou.tsx": __vite_glob_0_3, "./Pages/Forms/Standalone.tsx": __vite_glob_0_4, "./Pages/Pages/Show.tsx": __vite_glob_0_5, "./Pages/Posts/Show.tsx": __vite_glob_0_6, "./Pages/Projects/Show.tsx": __vite_glob_0_7 });
createServer((page) => createInertiaApp({
  page,
  render: renderToString,
  resolve: (name) => {
    const module = pages["./Pages/" + name + ".tsx"];
    if (!module) {
      throw new Error("Page not found: " + name);
    }
    const component = module.default;
    component.layout = component.layout ?? ((content) => /* @__PURE__ */ jsx(AppLayout, { children: content }));
    return component;
  },
  setup({ App: App2, props }) {
    return /* @__PURE__ */ jsx(App2, { ...props });
  }
}));
