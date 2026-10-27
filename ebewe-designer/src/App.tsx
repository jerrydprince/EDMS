import { useState } from 'react';
import { Shield, ChevronRight, CheckCircle2, Workflow, ArrowRight, Settings2, Plus, Trash2, Check, ArrowLeft, GitMerge, MoveDown, MoveUp, MoveRight, Layers, FileDigit } from 'lucide-react';

const HIERARCHY = [
  'Officer',
  'Senior Officer',
  'Principal Officer II',
  'Principal Officer I',
  'Assistant Chief',
  'Chief Officer',
  'Assistant Director',
  'Deputy Director',
  'Director',
  'Chief of Staff',
  'Special Adviser',
  'Accountant-General'
];

const DEPARTMENTS = [
  'Treasury Reforms',
  'Digital Transformation',
  'Finance & Accounts',
  'Internal Audit',
  'Checking Section',
  'Advances Section',
  'Other Charges',
  'Central Pay Office (CPO)'
];

const MODULES = ['Payment Vouchers', 'Procurement', 'Payroll', 'Audit Returns', 'External Mails'];

const ROUTING_TYPES = [
  { id: 'TOP_DOWN', label: 'Top-Down Assignment', icon: <MoveDown size={14}/>, desc: 'Hierarchy assigns task downwards' },
  { id: 'BOTTOM_UP', label: 'Bottom-Up Approval', icon: <MoveUp size={14}/>, desc: 'Staff pushes document upwards for approval' },
  { id: 'LATERAL', label: 'Lateral Handoff', icon: <MoveRight size={14}/>, desc: 'Passes to another department/section' },
  { id: 'PARALLEL', label: 'Parallel Verification', icon: <GitMerge size={14}/>, desc: 'Cross-departmental query (e.g. Confirm Vote)' },
  { id: 'LINEAR', label: 'Linear Assembly Line', icon: <ArrowRight size={14}/>, desc: 'Sequential specialized stamping (e.g. Audit -> CPO)' }
];

export default function WizardApp() {
  const [currentStep, setCurrentStep] = useState(1);
  const [isPublishing, setIsPublishing] = useState(false);
  const [isPublished, setIsPublished] = useState(false);
  
  const [workflowData, setWorkflowData] = useState({
    name: '',
    module: '',
    description: '',
    stages: [
      { 
        id: 1, 
        name: 'Initial Briefing', 
        routingType: 'TOP_DOWN',
        source: 'Accountant-General',
        destination: 'Action Staff'
      }
    ]
  });

  const nextStep = () => {
    if (currentStep === 3) {
      handlePublish();
    } else {
      setCurrentStep(prev => Math.min(prev + 1, 3));
    }
  };

  const prevStep = () => setCurrentStep(prev => Math.max(prev - 1, 1));

  const handlePublish = () => {
    setIsPublishing(true);
    setTimeout(() => {
      setIsPublishing(false);
      setIsPublished(true);
    }, 1500);
  };

  const resetWizard = () => {
    setIsPublished(false);
    setCurrentStep(1);
    setWorkflowData({
      name: '',
      module: '',
      description: '',
      stages: [
        { id: 1, name: 'Stage 1', routingType: 'TOP_DOWN', source: '', destination: '' }
      ]
    });
  };

  const addStage = () => {
    setWorkflowData(prev => ({
      ...prev,
      stages: [...prev.stages, { id: Date.now(), name: `Stage ${prev.stages.length + 1}`, routingType: 'TOP_DOWN', source: '', destination: '' }]
    }));
  };

  const removeStage = (id: number) => {
    setWorkflowData(prev => ({
      ...prev,
      stages: prev.stages.filter(s => s.id !== id)
    }));
  };

  const updateStage = (id: number, field: string, value: string) => {
    setWorkflowData(prev => ({
      ...prev,
      stages: prev.stages.map(s => s.id === id ? { ...s, [field]: value } : s)
    }));
  };

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
      <div className="w-full max-w-5xl bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col h-[90vh]">
        
        {/* Header */}
        <header className="bg-slate-900 text-white p-6 px-8 flex items-center justify-between flex-shrink-0">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-indigo-500 rounded-lg">
              <Layers size={24} className="text-white" />
            </div>
            <div>
              <h1 className="text-xl font-bold tracking-tight">EBEWE Configuration</h1>
              <p className="text-slate-400 text-sm">Stage-Based Workflow Builder</p>
            </div>
          </div>
          <div className="flex items-center gap-2 bg-slate-800 px-4 py-2 rounded-full border border-slate-700">
            <Shield size={16} className="text-emerald-400" />
            <span className="text-sm font-medium text-slate-200">Admin Mode</span>
          </div>
        </header>

        {/* Progress Bar */}
        <div className="bg-slate-100 border-b border-slate-200 px-8 py-4 flex items-center justify-between relative flex-shrink-0">
          <div className="absolute top-1/2 left-20 right-20 h-0.5 bg-slate-300 -translate-y-1/2 z-0"></div>
          
          {[1, 2, 3].map(step => (
            <div key={step} className={`relative z-10 flex flex-col items-center gap-2 transition-all duration-300 ${currentStep >= step ? 'opacity-100' : 'opacity-50'}`}>
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-colors duration-300 ${
                currentStep > step ? 'bg-indigo-600 border-indigo-600 text-white' :
                currentStep === step ? 'bg-white border-indigo-600 text-indigo-600 shadow-[0_0_0_4px_rgba(79,70,229,0.1)]' :
                'bg-white border-slate-300 text-slate-400'
              }`}>
                {currentStep > step ? <Check size={16} /> : step}
              </div>
              <span className={`text-xs font-semibold ${currentStep >= step ? 'text-slate-800' : 'text-slate-400'}`}>
                {step === 1 ? 'Workflow Details' : step === 2 ? 'Stage Configuration' : 'Review & Publish'}
              </span>
            </div>
          ))}
        </div>

        {/* Form Body */}
        <div className="flex-1 p-8 overflow-y-auto">
          
          {isPublished ? (
            <div className="animate-in zoom-in duration-500 flex flex-col items-center justify-center text-center h-full pt-10">
              <div className="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6">
                <CheckCircle2 size={48} className="text-green-600" />
              </div>
              <h2 className="text-3xl font-bold text-slate-800 mb-2">Workflow Published!</h2>
              <p className="text-slate-500 max-w-md mx-auto mb-8">
                Your complex, multi-stage workflow has been successfully serialized and mapped to the EBEWE backend engine.
              </p>
              <button 
                onClick={resetWizard}
                className="px-6 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-colors"
              >
                Create Another Workflow
              </button>
            </div>
          ) : (
            <>
              {currentStep === 1 && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-500 max-w-2xl mx-auto pt-6">
                  <h2 className="text-2xl font-bold text-slate-800 mb-2">Workflow Details</h2>
                  <p className="text-slate-500 mb-8">Define the basic metadata for this enterprise workflow.</p>
                  
                  <div className="space-y-6">
                    <div>
                      <label className="block text-sm font-semibold text-slate-700 mb-2">Workflow Name</label>
                      <input 
                        type="text" 
                        placeholder="e.g. Complete Payment Voucher Lifecycle"
                        className="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                        value={workflowData.name}
                        onChange={e => setWorkflowData({...workflowData, name: e.target.value})}
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-semibold text-slate-700 mb-2">Target Module / Document Type</label>
                      <select 
                        className="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm bg-white"
                        value={workflowData.module}
                        onChange={e => setWorkflowData({...workflowData, module: e.target.value})}
                      >
                        <option value="">Select a module...</option>
                        {MODULES.map(m => <option key={m} value={m}>{m}</option>)}
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                      <textarea 
                        rows={4}
                        placeholder="Briefly describe the end-to-end lifecycle..."
                        className="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm resize-none"
                        value={workflowData.description}
                        onChange={e => setWorkflowData({...workflowData, description: e.target.value})}
                      />
                    </div>
                  </div>
                </div>
              )}

              {currentStep === 2 && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-500 h-full flex flex-col">
                  <div className="flex items-center justify-between mb-6 flex-shrink-0">
                    <div>
                      <h2 className="text-2xl font-bold text-slate-800">Stage Configuration</h2>
                      <p className="text-slate-500 text-sm mt-1">Break down the complex workflow into interconnected logical stages.</p>
                    </div>
                    <button 
                      onClick={addStage}
                      className="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg font-semibold transition-colors text-sm border border-indigo-200 shadow-sm"
                    >
                      <Plus size={16} /> Add New Stage
                    </button>
                  </div>
                  
                  <div className="space-y-6 pb-10">
                    {workflowData.stages.map((stage, index) => (
                      <div key={stage.id} className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden relative group">
                        
                        <div className="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                          <div className="flex items-center gap-3">
                            <div className="w-6 h-6 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold border border-indigo-200">
                              {index + 1}
                            </div>
                            <input 
                              type="text" 
                              value={stage.name}
                              onChange={e => updateStage(stage.id, 'name', e.target.value)}
                              className="bg-transparent border-none font-bold text-slate-800 focus:ring-0 p-0 hover:bg-white hover:px-2 rounded transition-all"
                              placeholder="Stage Name"
                            />
                          </div>
                          
                          {index > 0 && (
                            <button 
                              onClick={() => removeStage(stage.id)}
                              className="text-slate-400 hover:text-rose-500 transition-colors"
                            >
                              <Trash2 size={18} />
                            </button>
                          )}
                        </div>

                        <div className="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                          
                          <div className="md:col-span-3">
                            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Routing Engine Logic</label>
                            <div className="grid grid-cols-2 lg:grid-cols-5 gap-3">
                              {ROUTING_TYPES.map(type => (
                                <div 
                                  key={type.id}
                                  onClick={() => updateStage(stage.id, 'routingType', type.id)}
                                  className={`cursor-pointer border rounded-lg p-3 flex flex-col items-center text-center gap-2 transition-all ${
                                    stage.routingType === type.id 
                                      ? 'border-indigo-500 bg-indigo-50 shadow-[0_0_0_1px_rgba(79,70,229,1)]' 
                                      : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'
                                  }`}
                                >
                                  <div className={`p-2 rounded-full ${stage.routingType === type.id ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600'}`}>
                                    {type.icon}
                                  </div>
                                  <span className={`text-xs font-bold ${stage.routingType === type.id ? 'text-indigo-900' : 'text-slate-700'}`}>{type.label}</span>
                                </div>
                              ))}
                            </div>
                          </div>

                          <div>
                            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Trigger / Source</label>
                            <select 
                              className="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 bg-white text-sm font-medium text-slate-700"
                              value={stage.source}
                              onChange={e => updateStage(stage.id, 'source', e.target.value)}
                            >
                              <option value="">Select source...</option>
                              <optgroup label="Hierarchical Roles">
                                {HIERARCHY.map(role => <option key={role} value={role}>{role}</option>)}
                              </optgroup>
                              <optgroup label="Departments / Sections">
                                {DEPARTMENTS.map(dept => <option key={dept} value={dept}>{dept}</option>)}
                              </optgroup>
                            </select>
                          </div>

                          <div className="flex items-center justify-center pt-6">
                            <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                              {ROUTING_TYPES.find(r => r.id === stage.routingType)?.icon || <ArrowRight size={16} className="text-slate-400" />}
                            </div>
                          </div>

                          <div>
                            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Destination / Target</label>
                            <select 
                              className="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 bg-white text-sm font-medium text-slate-700"
                              value={stage.destination}
                              onChange={e => updateStage(stage.id, 'destination', e.target.value)}
                            >
                              <option value="">Select destination...</option>
                              <optgroup label="Hierarchical Roles">
                                {HIERARCHY.map(role => <option key={role} value={role}>{role}</option>)}
                              </optgroup>
                              <optgroup label="Departments / Sections">
                                {DEPARTMENTS.map(dept => <option key={dept} value={dept}>{dept}</option>)}
                              </optgroup>
                            </select>
                          </div>

                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {currentStep === 3 && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-500 max-w-4xl mx-auto pt-6">
                  <div className="text-center mb-8">
                    <h2 className="text-3xl font-bold text-slate-800 mb-3">Review & Publish Workflow</h2>
                    <p className="text-slate-500">
                      Verify the multi-stage lifecycle below. EBEWE will generate automated monitoring dashboards for all Directors involved in this workflow.
                    </p>
                  </div>
                  
                  <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
                    <h3 className="font-bold text-xl text-slate-800 mb-1">{workflowData.name || 'Untitled Workflow'}</h3>
                    <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 text-xs font-bold mb-4 border border-indigo-100">
                      <FileDigit size={12} /> {workflowData.module || 'No Module Selected'}
                    </div>
                    <p className="text-slate-600 text-sm mb-6 pb-6 border-b border-slate-100">{workflowData.description}</p>
                    
                    <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Stage Execution Map</h4>
                    
                    <div className="space-y-4 relative">
                      <div className="absolute left-4 top-2 bottom-2 w-0.5 bg-slate-200 z-0"></div>
                      
                      {workflowData.stages.map((stage, idx) => {
                        const typeInfo = ROUTING_TYPES.find(r => r.id === stage.routingType);
                        return (
                          <div key={stage.id} className="relative z-10 flex gap-4">
                            <div className="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-200 border-2 border-white font-bold text-sm">
                              {idx + 1}
                            </div>
                            <div className="bg-slate-50 rounded-lg p-4 border border-slate-200 flex-1 shadow-sm">
                              <div className="flex items-center justify-between mb-2">
                                <span className="font-bold text-slate-800">{stage.name}</span>
                                <span className="text-xs font-semibold text-slate-500 bg-white px-2 py-1 rounded border border-slate-200 flex items-center gap-1.5">
                                  {typeInfo?.icon} {typeInfo?.label}
                                </span>
                              </div>
                              <div className="flex items-center text-sm font-medium text-slate-600 gap-2 mt-3 bg-white p-2 rounded border border-slate-100 inline-flex">
                                <span className="text-indigo-700">{stage.source || '...'}</span>
                                <ArrowRight size={14} className="text-slate-400" />
                                <span className="text-emerald-700">{stage.destination || '...'}</span>
                              </div>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              )}
            </>
          )}
        </div>

        {/* Footer Actions */}
        {!isPublished && (
          <div className="bg-slate-50 border-t border-slate-200 p-6 px-8 flex items-center justify-between flex-shrink-0">
            <button 
              onClick={prevStep}
              disabled={currentStep === 1 || isPublishing}
              className={`flex items-center gap-2 px-6 py-3 rounded-xl font-bold transition-all ${
                currentStep === 1 || isPublishing ? 'opacity-50 cursor-not-allowed text-slate-400' : 'text-slate-700 hover:bg-slate-200'
              }`}
            >
              <ArrowLeft size={18} /> Back
            </button>
            
            <button 
              onClick={nextStep}
              disabled={isPublishing}
              className={`flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all ${isPublishing ? 'opacity-75 cursor-wait' : ''}`}
            >
              {isPublishing ? 'Publishing Engine Logic...' : currentStep === 3 ? 'Publish Workflow' : 'Next Step'} 
              {!isPublishing && currentStep < 3 && <ChevronRight size={18} />}
            </button>
          </div>
        )}

      </div>
    </div>
  );
}
