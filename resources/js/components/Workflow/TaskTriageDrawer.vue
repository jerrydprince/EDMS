<template>
  <Transition name="drawer-fade">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
      <!-- Backdrop -->
      <div 
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300"
        @click="closeDrawer"
      ></div>

      <!-- Slide-over Drawer Panel -->
      <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
        <Transition name="drawer-slide">
          <div 
            v-if="isOpen" 
            class="w-screen max-w-3xl xl:max-w-4xl bg-white shadow-2xl flex flex-col h-full border-l border-gray-100 transform transition-transform duration-300 ease-in-out"
          >
            <!-- Drawer Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-slate-50 via-white to-blue-50/40 border-b border-gray-100 flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                  <span 
                    v-if="task.tracking_id" 
                    class="font-mono text-xs px-2.5 py-1 bg-white border border-gray-200 text-gray-600 rounded-md shadow-2xs font-semibold"
                  >
                    #{{ task.tracking_id }}
                  </span>

                  <!-- SLA Countdown Badge -->
                  <span 
                    :class="slaBadgeClass"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shadow-2xs"
                  >
                    <span :class="slaDotClass" class="w-2 h-2 rounded-full"></span>
                    {{ slaLabel }}
                  </span>

                  <!-- Priority Badge -->
                  <span 
                    :class="priorityBadgeClass" 
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                  >
                    {{ priorityLabel }}
                  </span>

                  <!-- Status Badge -->
                  <span 
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold capitalize bg-gray-100 text-gray-700"
                  >
                    {{ task.status || 'Pending' }}
                  </span>

                  <!-- Active Workflow Step Badge -->
                  <span 
                    v-if="task.workflow_version_id && activeStepName"
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1 shadow-2xs"
                  >
                    <Icon icon="lucide:git-commit" width="13" />
                    <span>{{ activeStepName }}</span>
                  </span>
                </div>

                <h2 class="text-xl font-bold text-gray-900 leading-snug truncate" :title="task.title">
                  {{ task.title }}
                </h2>
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-3">
                  <span>Created: {{ formatDate(task.created_at) }}</span>
                  <span v-if="task.due_date">• Deadline: {{ formatDate(task.due_date) }}</span>
                </p>
              </div>

              <!-- Header Controls (Prev/Next & Close) -->
              <div class="flex items-center gap-2 shrink-0">
                <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200">
                  <button 
                    @click="$emit('prev')" 
                    :disabled="!hasPrev"
                    title="Previous Task (Up / K)" 
                    class="p-1.5 rounded text-gray-600 hover:text-gray-900 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition-all"
                  >
                    <Icon icon="lucide:chevron-up" width="18" />
                  </button>
                  <button 
                    @click="$emit('next')" 
                    :disabled="!hasNext"
                    title="Next Task (Down / J)" 
                    class="p-1.5 rounded text-gray-600 hover:text-gray-900 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition-all"
                  >
                    <Icon icon="lucide:chevron-down" width="18" />
                  </button>
                </div>

                <button 
                  @click="closeDrawer" 
                  title="Close (Esc)" 
                  class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <Icon icon="lucide:x" width="20" />
                </button>
              </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center border-b border-gray-200 bg-gray-50/60 px-6 shrink-0 overflow-x-auto">
              <button 
                @click="activeTab = 'preview'" 
                class="py-3 px-4 font-semibold text-sm transition-all border-b-2 flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'preview' ? 'border-primary text-primary bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'"
              >
                <Icon icon="lucide:file-text" width="16" />
                <span>Document Preview</span>
                <span v-if="allDocs.length > 0" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-mono">
                  {{ allDocs.length }}
                </span>
              </button>

              <button 
                @click="activeTab = 'minutes'" 
                class="py-3 px-4 font-semibold text-sm transition-all border-b-2 flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'minutes' ? 'border-primary text-primary bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'"
              >
                <Icon icon="lucide:file-signature" width="16" />
                <span>Minute Sheet & Timeline</span>
                <span v-if="minutesList.length > 0" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-mono">
                  {{ minutesList.length }}
                </span>
              </button>

              <!-- Tab 3: Submissions & Review (Visible when task has submissions) -->
              <button 
                v-if="hasSubmissions"
                @click="activeTab = 'submissions'" 
                class="py-3 px-4 font-semibold text-sm transition-all border-b-2 flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'submissions' ? 'border-emerald-600 text-emerald-700 bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'"
              >
                <Icon icon="lucide:file-check" width="16" class="text-emerald-600" />
                <span>Submissions & Review</span>
                <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-mono font-bold">
                  {{ submissionCount }}
                </span>
              </button>

              <!-- Tab 4: Smart Task Discussions -->
              <button 
                @click="activeTab = 'discussions'" 
                class="py-3 px-4 font-semibold text-sm transition-all border-b-2 flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'discussions' ? 'border-primary text-primary bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'"
              >
                <Icon icon="lucide:message-square" width="16" />
                <span>Smart Discussions</span>
                <span v-if="commentsCount > 0" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-mono font-bold">
                  {{ commentsCount }}
                </span>
              </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
              <!-- TAB 1: DOCUMENT PREVIEW -->
              <div v-show="activeTab === 'preview'" class="space-y-4">
                <!-- Multiple documents selector pill row -->
                <div v-if="allDocs.length > 1" class="flex items-center gap-2 overflow-x-auto pb-1">
                  <button 
                    v-for="(doc, idx) in allDocs" 
                    :key="idx"
                    @click="selectedDocIndex = idx"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5"
                    :class="selectedDocIndex === idx ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                  >
                    <Icon icon="lucide:file" width="14" />
                    <span class="truncate max-w-[150px]">{{ doc.file_name || doc.name || 'Document ' + (idx + 1) }}</span>
                  </button>
                </div>

                <!-- Active Document Details & Action Bar -->
                <div v-if="activeDoc" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center justify-between gap-4">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                      <Icon icon="lucide:file-text" width="22" />
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-semibold text-gray-800 truncate text-sm" :title="activeDoc.file_name || activeDoc.name">
                        {{ activeDoc.file_name || activeDoc.name || 'Attached Document' }}
                      </h4>
                      <p class="text-xs text-gray-400 mt-0.5">{{ activeDoc.size || 'Attachment' }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <a 
                      v-if="docUrl"
                      :href="docUrl" 
                      target="_blank" 
                      class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition-colors flex items-center gap-1.5 shadow-2xs"
                    >
                      <Icon icon="lucide:external-link" width="14" />
                      <span>New Tab</span>
                    </a>
                    <a 
                      v-if="docUrl"
                      :href="docUrl" 
                      download 
                      class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-semibold transition-all flex items-center gap-1.5"
                    >
                      <Icon icon="lucide:download" width="14" />
                      <span>Download</span>
                    </a>
                  </div>
                </div>

                <!-- Document Embedded Viewer -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[500px] flex flex-col">
                  <div v-if="isPdf && docUrl" class="w-full flex-1 min-h-[550px]">
                    <iframe 
                      :src="docUrl" 
                      class="w-full h-[600px] border-0 rounded-2xl" 
                      title="Document Preview"
                    ></iframe>
                  </div>
                  <div v-else-if="isImage && docUrl" class="p-4 flex items-center justify-center bg-gray-100/50">
                    <img :src="docUrl" alt="Document Preview" class="max-h-[600px] rounded-lg object-contain shadow-sm" />
                  </div>
                  <div v-else class="flex flex-col items-center justify-center p-12 text-center text-gray-500 my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mb-4">
                      <Icon icon="lucide:file-question" width="32" />
                    </div>
                    <h3 class="font-bold text-gray-700 text-base">No Preview Available</h3>
                    <p class="text-xs text-gray-400 max-w-sm mt-1">
                      This task has no embedded file attachment or the format does not support in-browser preview.
                    </p>
                    <a 
                      v-if="docUrl"
                      :href="docUrl" 
                      download
                      class="mt-4 px-4 py-2 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2"
                    >
                      <Icon icon="lucide:download" width="14" />
                      <span>Download Document</span>
                    </a>
                  </div>
                </div>
              </div>

              <!-- TAB 2: MINUTE SHEET & TIMELINE -->
              <div v-show="activeTab === 'minutes'" class="space-y-4">
                <!-- Flight Path Metro Map -->
                <WorkflowMetroMap v-if="task && task.id" :task="task" class="shadow-xs" />

                <!-- Living Docket Sheet & Audit Timeline -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[450px]">
                  <DocketTimeline v-if="task && task.id" :task="task" class="h-[520px]" />
                </div>
              </div>

              <!-- TAB 3: SUBMISSIONS & REVIEW -->
              <div v-show="activeTab === 'submissions'" class="space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                  <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                      <Icon icon="lucide:file-check" class="text-emerald-600" width="20" />
                      <h3 class="font-bold text-gray-800 text-base">Submissions & Output Review</h3>
                    </div>
                    <span class="text-xs font-mono text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 font-semibold">
                      {{ submissionCount }} {{ submissionCount === 1 ? 'Submission' : 'Submissions' }}
                    </span>
                  </div>

                  <div v-if="submissionList.length > 0" class="space-y-5">
                    <div 
                      v-for="(sub, sIdx) in submissionList" 
                      :key="sIdx"
                      class="bg-gray-50/70 rounded-xl p-4 border border-gray-200/80 space-y-3"
                    >
                      <!-- Submitter header -->
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                          <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            {{ (sub.user?.first_name || 'U').charAt(0) }}
                          </div>
                          <div>
                            <p class="font-bold text-gray-900 text-xs">
                              {{ sub.user ? sub.user.first_name + ' ' + sub.user.last_name : 'Assigned Staff' }}
                            </p>
                            <p class="text-[10px] text-gray-500">
                              {{ sub.user?.department?.title || 'Operational Staff' }}
                            </p>
                          </div>
                        </div>
                        <span class="text-[11px] text-gray-400 font-mono">
                          {{ formatDateTime(sub.created_at) }}
                        </span>
                      </div>

                      <!-- Submitter Notes / Description -->
                      <div v-if="sub.description" class="bg-white p-3 rounded-lg border border-gray-200 text-xs text-gray-700 whitespace-pre-wrap leading-relaxed shadow-2xs">
                        {{ sub.description }}
                      </div>

                      <!-- Attached Output Files -->
                      <div v-if="sub.task_submission_files && sub.task_submission_files.length > 0" class="space-y-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Submitted Deliverables</p>
                        <div class="grid grid-cols-1 gap-2">
                          <div 
                            v-for="(sFile, fIdx) in sub.task_submission_files" 
                            :key="fIdx"
                            class="bg-white p-2.5 rounded-lg border border-gray-200 flex items-center justify-between gap-3 shadow-2xs hover:border-emerald-300 transition-colors"
                          >
                            <div class="flex items-center gap-2.5 min-w-0">
                              <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <Icon icon="lucide:paperclip" width="16" />
                              </div>
                              <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-800 truncate" :title="sFile.name || sFile.file_name">
                                  {{ sFile.name || sFile.file_name || 'Deliverable File' }}
                                </p>
                                <p class="text-[10px] text-gray-400">{{ sFile.size || 'Attachment' }}</p>
                              </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                              <a 
                                v-if="getDeliverableUrl(sFile)" 
                                :href="getDeliverableUrl(sFile)" 
                                target="_blank"
                                class="px-2.5 py-1 text-xs rounded-md border border-gray-200 hover:bg-gray-50 text-gray-700 flex items-center gap-1"
                              >
                                <Icon icon="lucide:external-link" width="12" />
                                <span>View</span>
                              </a>
                              <a 
                                v-if="getDeliverableUrl(sFile)" 
                                :href="getDeliverableUrl(sFile)" 
                                download
                                class="px-2.5 py-1 text-xs rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 flex items-center gap-1 font-semibold"
                              >
                                <Icon icon="lucide:download" width="12" />
                                <span>Download</span>
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-else class="text-center py-10 text-gray-400">
                    <Icon icon="lucide:file-question" width="36" class="mx-auto mb-2 opacity-50 text-gray-300" />
                    <p class="text-sm">No task submission files uploaded yet.</p>
                  </div>
                </div>
              </div>

              <!-- TAB 4: SMART TASK DISCUSSIONS -->
              <div v-if="activeTab === 'discussions'" class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-[600px]">
                  <TaskDiscussions :task="task" class="w-full flex-1" />
                </div>
              </div>
            </div>

            <!-- Drawer Footer (Smart Suggestion & Actions) -->
            <div class="p-5 bg-white border-t border-gray-200 space-y-3 shrink-0 shadow-lg">
              
              <!-- AGF 1-Click Executive Minuting & Directorate Routing Cockpit -->
              <div 
                v-if="isAgf && !isSubordinateUnderReview" 
                class="p-4 bg-gradient-to-r from-amber-500/10 via-amber-50 to-yellow-50 border-2 border-amber-300 rounded-2xl space-y-3 shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black text-amber-950 uppercase tracking-widest flex items-center gap-1.5">
                    <Icon icon="lucide:crown" class="text-amber-600" width="16" />
                    AGF Executive Minuting & Routing
                  </span>
                  <span class="text-[10px] text-amber-800 font-bold bg-amber-200/80 px-2 py-0.5 rounded-full border border-amber-300">
                    5-Second Direct Route
                  </span>
                </div>

                <!-- Directive Chips -->
                <div class="flex flex-wrap gap-1.5">
                  <button
                    type="button"
                    v-for="(dir, idx) in executiveDirectives"
                    :key="idx"
                    @click="selectedDirective = dir"
                    class="text-[11px] px-2.5 py-1 rounded-lg border font-medium transition-all cursor-pointer"
                    :class="selectedDirective === dir ? 'bg-amber-600 text-white border-amber-600 shadow-2xs font-bold' : 'bg-white text-slate-700 border-amber-200 hover:bg-amber-100/50'"
                  >
                    {{ dir }}
                  </button>
                </div>

                <!-- Directive Input -->
                <div class="relative">
                  <input 
                    v-model="selectedDirective"
                    type="text" 
                    placeholder="Executive directive / instructions for the Directorate..."
                    class="w-full text-xs p-2.5 bg-white border border-amber-300 rounded-xl outline-none focus:ring-2 focus:ring-amber-400 font-serif italic text-slate-800"
                  />
                </div>

                <!-- Target Director Selector & Immediate Route Button -->
                <div class="flex items-center gap-2 pt-1">
                  <select 
                    v-model="selectedDirectorId"
                    class="flex-1 text-xs p-2.5 bg-white border border-amber-300 rounded-xl font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-amber-400"
                  >
                    <option :value="null">-- Select Operational Directorate --</option>
                    <option v-for="dir in departmentalDirectors" :key="dir.id" :value="dir.id">
                      Director, {{ dir.department?.title || 'Directorate' }} ({{ dir.first_name }} {{ dir.last_name }})
                    </option>
                  </select>

                  <button
                    type="button"
                    :disabled="!selectedDirectorId || isExecutiveRouting"
                    @click="executeAgfRoute"
                    class="bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 hover:to-yellow-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed shrink-0 cursor-pointer"
                  >
                    <Icon v-if="isExecutiveRouting" icon="lucide:loader-2" class="animate-spin" width="16" />
                    <Icon v-else icon="lucide:send" width="16" />
                    <span>Route & Minute</span>
                  </button>
                </div>
              </div>

              <!-- Statutory Generic Workflow Routing Banner -->
              <div 
                v-if="task.workflow_version_id && (activeStepName || primaryGenericForwardAction)" 
                class="p-3.5 bg-gradient-to-r from-emerald-50/90 via-teal-50/80 to-blue-50/70 rounded-xl border border-emerald-200/90 flex items-center justify-between gap-3 shadow-2xs"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <Icon icon="lucide:git-commit" width="18" />
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="text-xs font-bold text-gray-900 truncate">
                        {{ activeStepName || 'Official Statutory Workflow' }}
                      </span>
                      <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-300">
                        Milestone 1 • Submission
                      </span>
                    </div>
                    <p class="text-[11px] text-gray-600 truncate mt-0.5" v-if="primaryGenericForwardAction">
                      Next Step: <strong class="text-emerald-800">{{ primaryGenericForwardAction.to_step?.name || primaryGenericForwardAction.action_name }}</strong>
                    </p>
                    <p class="text-[11px] text-emerald-700 font-medium truncate mt-0.5" v-else-if="isGenericActionCompleted">
                      Docket successfully submitted and awaiting review at this stage.
                    </p>
                  </div>
                </div>

                <!-- Quick Action Button in Banner -->
                <button 
                  v-if="primaryGenericForwardAction"
                  @click="openGenericSignModal = true"
                  class="shrink-0 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs px-3.5 py-2 rounded-lg transition-all shadow-sm hover:shadow flex items-center gap-1.5 cursor-pointer"
                >
                  <Icon icon="lucide:pen-tool" width="14" />
                  <span>{{ primaryGenericForwardAction.action_name || 'Submit to Director' }}</span>
                </button>
              </div>

              <!-- Smart Routing Recommendation Banner (Shown for legacy unit tasks ONLY, not generic workflows) -->
              <div 
                v-else-if="suggestedRouting && !isAgf && !task.workflow_version_id" 
                class="p-3.5 bg-gradient-to-r from-blue-50/80 to-indigo-50/60 rounded-xl border border-blue-200/80 flex items-center justify-between gap-3 shadow-2xs"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-sm">
                    <Icon icon="lucide:sparkles" width="18" />
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="text-xs font-bold text-gray-900 truncate">
                        {{ suggestedRouting.name }}
                      </span>
                      <span class="text-[10px] bg-blue-100 text-blue-700 font-bold px-1.5 py-0.5 rounded">
                        {{ suggestedRouting.designation }}
                      </span>
                      <span v-if="suggestedRouting.is_acting" class="text-[10px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded">
                        Acting Head
                      </span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate mt-0.5">
                      {{ suggestedRouting.reason }}
                    </p>
                  </div>
                </div>

                <!-- Action Button for Smart Route -->
                <button 
                  v-if="isUnitStaff"
                  @click="$emit('submit', task)" 
                  class="shrink-0 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold text-xs px-3.5 py-2 rounded-lg transition-all shadow-sm hover:shadow flex items-center gap-1.5"
                >
                  <Icon icon="lucide:send" width="14" />
                  <span>Submit to {{ suggestedRouting.role || 'Assistant Director' }}</span>
                </button>
                <button 
                  v-else-if="canAssign"
                  @click="$emit('quick-route', { task: task, suggested: suggestedRouting })" 
                  class="shrink-0 bg-primary hover:bg-blue-700 text-white font-semibold text-xs px-3.5 py-2 rounded-lg transition-all shadow-sm hover:shadow flex items-center gap-1.5"
                >
                  <Icon icon="lucide:corner-up-right" width="14" />
                  <span>Route Now</span>
                </button>
              </div>

              <!-- Main Action Buttons -->
              <div class="flex items-center justify-between gap-3 pt-1">
                <Link 
                  :href="'/dashboard/view-task/' + task.id"
                  class="text-xs font-semibold text-gray-600 hover:text-primary px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors flex items-center gap-1.5 shrink-0"
                >
                  <Icon icon="lucide:maximize-2" width="14" />
                  <span>Full Task Page</span>
                </Link>

                <div class="flex items-center gap-2 flex-wrap justify-end">
                  <!-- 0. GENERIC WORKFLOW ACTIONS (Official Statutory Workflows) -->
                  <template v-if="task.workflow_version_id">
                    <div v-if="isLoadingGenericState" class="flex items-center gap-2 text-xs text-gray-500 font-medium px-3 py-2">
                      <Icon icon="lucide:loader-2" class="animate-spin text-emerald-600" width="16" />
                      <span>Checking workflow actions...</span>
                    </div>

                    <!-- Submitted Status Badge -->
                    <span 
                      v-else-if="isGenericActionCompleted" 
                      class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200"
                    >
                      <Icon icon="lucide:check-circle" width="16" class="text-emerald-600" />
                      <span>Submitted • Docket with Reviewing Authority</span>
                    </span>

                    <template v-else-if="primaryGenericForwardAction">
                      <!-- Return Action if available -->
                      <button 
                        v-if="primaryGenericReturnAction"
                        @click="openGenericReturnModal = true"
                        class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer"
                        title="Return Docket with Revision Directives"
                      >
                        <Icon icon="lucide:undo-2" width="16" />
                        <span>{{ primaryGenericReturnAction.action_name || primaryGenericReturnAction.name }}</span>
                      </button>

                      <!-- Primary Forward Action (e.g. Submit to Departmental Director) -->
                      <button 
                        @click="openGenericSignModal = true"
                        class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2 cursor-pointer"
                        :title="primaryGenericForwardAction.action_name || primaryGenericForwardAction.name"
                      >
                        <Icon icon="lucide:send" width="16" />
                        <span>{{ primaryGenericForwardAction.action_name || primaryGenericForwardAction.name }}</span>
                      </button>
                    </template>
                  </template>

                  <!-- DIRECT EXECUTION: REVIEW ACTIONS (When subordinate submitted task for review) -->
                  <template v-else-if="isSubordinateUnderReview">
                    <!-- Request Revision (Send back to subordinate for amendment) -->
                    <button 
                      @click="$emit('request-revision', task)" 
                      class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Request Revision from Subordinate"
                    >
                      <Icon icon="mdi:file-document-edit-outline" width="16" />
                      <span>Request Revision</span>
                    </button>

                    <!-- Query Task / Request Info -->
                    <button 
                      @click="$emit('hold', task)" 
                      class="bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Query Subordinate / Request Info"
                    >
                      <Icon icon="mdi:message-question-outline" width="16" />
                      <span>Query</span>
                    </button>

                    <!-- Reject / Return Task -->
                    <button 
                      @click="$emit('return', task)" 
                      class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Reject / Return Submission"
                    >
                      <Icon icon="lucide:undo-2" width="16" />
                      <span>Reject</span>
                    </button>

                    <!-- Approve Task (Upward to Supervisor or Final by AGF) -->
                    <button 
                      :disabled="task.is_approved"
                      @click="task.is_approved ? null : $emit('approve', task)" 
                      :class="[
                        'text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-1.5',
                        task.is_approved 
                          ? 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed opacity-60 shadow-none' 
                          : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white hover:shadow-md cursor-pointer'
                      ]"
                      :title="task.is_approved ? 'Task submission has been approved' : approveButtonLabel"
                    >
                      <Icon :icon="task.is_approved ? 'lucide:check-check' : 'mdi:check-decagram'" width="16" />
                      <span>{{ task.is_approved ? 'Approved' : approveButtonLabel }}</span>
                    </button>
                  </template>

                  <!-- DIRECT EXECUTION: SUPERVISOR WORKING ACTIONS (When working directly on task or assigning downward) -->
                  <template v-else-if="canAssign && isTaskActive">
                    <!-- Hold / Request Info -->
                    <button 
                      @click="$emit('hold', task)" 
                      class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Put Task On Hold & Request Information"
                    >
                      <Icon icon="mdi:pause-circle-outline" width="16" />
                      <span>Hold</span>
                    </button>

                    <!-- Return Task (For Director, Deputy Director, Assistant Director returning upward; AGF has no upline) -->
                    <button 
                      v-if="!isAgf && hasUpline"
                      @click="$emit('return', task)" 
                      class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Return Task to Assigner"
                    >
                      <Icon icon="lucide:undo-2" width="16" />
                      <span>Return</span>
                    </button>

                    <!-- Submit Upward (For Directors, Deputy Directors, Assistant Directors when working directly on task; AGF never submits) -->
                    <button 
                      v-if="!isAgf && hasUpline && isAssignedToMe"
                      @click="$emit('submit', task)" 
                      class="bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center gap-1.5"
                      title="Submit Task Upward with Digital Signature"
                    >
                      <Icon icon="mdi:tick-circle-outline" width="16" />
                      <span>Submit Upward</span>
                    </button>

                    <!-- Assign / Reassign to Subordinate -->
                    <button 
                      :disabled="!canReassign"
                      :title="reassignTitle"
                      @click="canReassign && $emit('assign', task)" 
                      :class="[
                        'text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2',
                        !canReassign 
                          ? 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-60 shadow-none' 
                          : 'bg-emerald-600 hover:bg-emerald-700 text-white hover:shadow-md cursor-pointer'
                      ]"
                    >
                      <Icon icon="lucide:user-plus" width="16" />
                      <span>{{ hasAssignments ? 'Reassign Task' : 'Assign Task' }}</span>
                    </button>
                  </template>

                  <!-- DIRECT EXECUTION: UNIT STAFF ACTIONS (Submit, Hold, Return) -->
                  <template v-else-if="isUnitStaff && isTaskActive">
                    <!-- Hold Task (Request Info) -->
                    <button 
                      @click="$emit('hold', task)" 
                      class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold px-3.5 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Put Task On Hold & Request Information"
                    >
                      <Icon icon="mdi:pause-circle-outline" width="16" />
                      <span>Hold</span>
                    </button>

                    <!-- Return Task -->
                    <button 
                      @click="$emit('return', task)" 
                      class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold px-3.5 py-2.5 rounded-xl transition-all shadow-2xs flex items-center gap-1.5"
                      title="Return Task to Assigner"
                    >
                      <Icon icon="lucide:undo-2" width="16" />
                      <span>Return</span>
                    </button>

                    <!-- Submit Task -->
                    <button 
                      @click="$emit('submit', task)" 
                      class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center gap-1.5"
                      title="Submit Task with Digital Signature"
                    >
                      <Icon icon="mdi:tick-circle-outline" width="16" />
                      <span>Submit Task</span>
                    </button>
                  </template>

                  <button 
                    @click="closeDrawer" 
                    class="border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors cursor-pointer"
                  >
                    Done
                  </button>
                </div>
              </div>
            </div>

          </div>
        </Transition>
      </div>
    </div>
  </Transition>

  <!-- Generic Workflow Digital Signature Modal -->
  <Dialog 
    v-model:visible="openGenericSignModal" 
    modal 
    :header="false" 
    :style="{ width: '32rem', maxWidth: '95vw' }"
    :pt="{
      root: { class: 'border-0 rounded-2xl overflow-hidden shadow-2xl bg-white' },
      mask: { class: 'bg-black/60 backdrop-blur-xs' },
      content: { class: 'p-0' }
    }"
  >
    <div class="bg-gradient-to-br from-emerald-800 via-teal-800 to-emerald-950 p-6 text-white relative">
      <button 
        @click="openGenericSignModal = false" 
        class="absolute top-4 right-4 text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer"
      >
        <Icon icon="lucide:x" width="20" />
      </button>
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
          <Icon icon="lucide:pen-tool" width="22" />
        </div>
        <div>
          <h3 class="font-bold text-base text-white">Digital Signature & Minuting</h3>
          <p class="text-xs text-emerald-200 mt-0.5">
            {{ primaryGenericForwardAction?.action_name || 'Forward Workflow Task' }}
          </p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-4">
      <!-- Next Destination Info -->
      <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center gap-3">
        <Icon icon="lucide:shield-check" class="text-emerald-700 shrink-0" width="20" />
        <div class="text-xs">
          <p class="font-bold text-emerald-950">Statutory Progression</p>
          <p class="text-emerald-800">
            Forwarding directly to: <strong>{{ primaryGenericForwardAction?.to_step?.name || 'Departmental Director' }}</strong>
          </p>
        </div>
      </div>

      <!-- Remarks / Minuting -->
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Official Minute / Submission Remarks</label>
        <textarea 
          v-model="genericSignRemark" 
          rows="3" 
          placeholder="Respectfully submitted for your consideration and recommendation, sir."
          class="w-full text-xs p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all resize-none"
        ></textarea>
      </div>

      <!-- 4-Digit Digital Signature PIN -->
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Enter 4-Digit Signature PIN</label>
        <div class="relative">
          <input 
            v-model="genericSignPin" 
            type="password" 
            maxlength="4" 
            placeholder="••••"
            @keyup.enter="executeGenericForward"
            class="w-full text-center tracking-[0.5em] text-lg font-bold p-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
          />
          <Icon icon="lucide:lock" class="absolute right-3.5 top-3.5 text-gray-400" width="16" />
        </div>
        <p v-if="genericSignPinError" class="text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1">
          <Icon icon="lucide:alert-circle" width="14" />
          <span>{{ genericSignPinError }}</span>
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-2.5 pt-2">
        <button 
          type="button" 
          @click="openGenericSignModal = false"
          class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="button" 
          :disabled="isProcessingGenericSign || !genericSignPin"
          @click="executeGenericForward"
          class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
          <Icon v-if="isProcessingGenericSign" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="lucide:send" width="16" />
          <span>{{ isProcessingGenericSign ? 'Signing & Submitting...' : 'Sign & Submit Docket' }}</span>
        </button>
      </div>
    </div>
  </Dialog>

  <!-- Generic Workflow Return Modal -->
  <Dialog 
    v-model:visible="openGenericReturnModal" 
    modal 
    :header="false" 
    :style="{ width: '32rem', maxWidth: '95vw' }"
    :pt="{
      root: { class: 'border-0 rounded-2xl overflow-hidden shadow-2xl bg-white' },
      mask: { class: 'bg-black/60 backdrop-blur-xs' },
      content: { class: 'p-0' }
    }"
  >
    <div class="bg-gradient-to-br from-rose-800 via-red-800 to-rose-950 p-6 text-white relative">
      <button 
        @click="openGenericReturnModal = false" 
        class="absolute top-4 right-4 text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer"
      >
        <Icon icon="lucide:x" width="20" />
      </button>
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
          <Icon icon="lucide:undo-2" width="22" />
        </div>
        <div>
          <h3 class="font-bold text-base text-white">Return Application</h3>
          <p class="text-xs text-rose-200 mt-0.5">Return docket for amendment</p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Directives / Reason for Return <span class="text-rose-500">*</span></label>
        <textarea 
          v-model="genericReturnReason" 
          rows="3" 
          placeholder="Specify reason or instructions for amendment..."
          class="w-full text-xs p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all resize-none"
        ></textarea>
      </div>

      <div class="flex items-center justify-end gap-2.5 pt-2">
        <button 
          type="button" 
          @click="openGenericReturnModal = false"
          class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="button" 
          :disabled="isProcessingGenericReturn || !genericReturnReason.trim()"
          @click="executeGenericReturn"
          class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-700 hover:to-red-800 text-white text-xs font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
          <Icon v-if="isProcessingGenericReturn" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="lucide:undo-2" width="16" />
          <span>{{ isProcessingGenericReturn ? 'Returning...' : 'Return Docket' }}</span>
        </button>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";
import Dialog from "primevue/dialog";
import TaskDiscussions from "../../Pages/Workflow/Components/TaskDiscussions.vue";
import WorkflowMetroMap from "./WorkflowMetroMap.vue";
import DocketTimeline from "./DocketTimeline.vue";

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  task: {
    type: Object,
    default: () => ({}),
  },
  hasPrev: {
    type: Boolean,
    default: false,
  },
  hasNext: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "close", 
  "prev", 
  "next", 
  "assign", 
  "quick-route", 
  "submit", 
  "hold", 
  "return",
  "approve",
  "request-revision",
  "refresh"
]);

const page = usePage();
const activeTab = ref("preview");
const selectedDocIndex = ref(0);

// AGF Executive Minuting & Routing State
const departmentalDirectors = ref([]);
const selectedDirectorId = ref(null);
const selectedDirective = ref("Please Treat & Advise");
const isExecutiveRouting = ref(false);

const executiveDirectives = [
  "Please Treat & Advise",
  "For Necessary Action & Report Back",
  "Please Investigate & Brief Me",
  "Process for Payment",
  "Urgent: Treat within 24 Hours"
];

const fetchDirectors = async () => {
  if (departmentalDirectors.value.length > 0) return;
  try {
    const res = await axios.get("/dashboard/get-leave-relief-officers");
    departmentalDirectors.value = res.data || [];
  } catch (e) {
    console.error("Failed to load departmental directors", e);
  }
};

const executeAgfRoute = async () => {
  if (!selectedDirectorId.value) return;
  isExecutiveRouting.value = true;
  try {
    await axios.post("/dashboard/executive-route-task", {
      task_id: props.task.id,
      director_user_id: selectedDirectorId.value,
      directive: selectedDirective.value
    });
    emit("refresh");
    closeDrawer();
  } catch (e) {
    alert("Failed to route docket: " + (e.response?.data?.message || e.message));
  } finally {
    isExecutiveRouting.value = false;
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchDirectors();
  }
}, { immediate: true });

// Generic Workflow Integration (Cross-Departmental / Statutory Workflows)
const genericActiveSteps = ref([]);
const genericAvailableActions = ref([]);
const isLoadingGenericState = ref(false);

const openGenericSignModal = ref(false);
const openGenericReturnModal = ref(false);
const genericSignRemark = ref("Respectfully submitted for your consideration and recommendation, sir.");
const genericSignPin = ref("");
const genericSignPinError = ref("");
const isProcessingGenericSign = ref(false);

const genericReturnReason = ref("");
const isProcessingGenericReturn = ref(false);

const fetchGenericState = async () => {
  if (!props.task?.workflow_version_id || !props.task?.id) {
    genericActiveSteps.value = [];
    genericAvailableActions.value = [];
    return;
  }
  isLoadingGenericState.value = true;
  try {
    const res = await axios.get(`/dashboard/generic-workflow/tasks/${props.task.id}/state`);
    if (res.data) {
      genericActiveSteps.value = res.data.active_steps || [];
      genericAvailableActions.value = res.data.available_actions || [];
    }
  } catch (err) {
    console.error("Failed to fetch generic workflow state in TaskTriageDrawer", err);
  } finally {
    isLoadingGenericState.value = false;
  }
};

const activeStepName = computed(() => {
  if (genericActiveSteps.value.length > 0) {
    const s = genericActiveSteps.value[0];
    return s.step?.name || s.workflow_step?.name || '';
  }
  return '';
});

const primaryGenericForwardAction = computed(() => {
  if (!genericAvailableActions.value || genericAvailableActions.value.length === 0) return null;
  return genericAvailableActions.value.find(a => !a.is_auto_return && a.action_type !== 'return') || genericAvailableActions.value[0];
});

const primaryGenericReturnAction = computed(() => {
  if (!genericAvailableActions.value || genericAvailableActions.value.length === 0) return null;
  return genericAvailableActions.value.find(a => a.is_auto_return || a.action_type === 'return');
});

const isGenericActionCompleted = computed(() => {
  if (!props.task?.workflow_version_id) return false;
  if ((props.task?.status || '').toLowerCase() === 'completed') return true;
  if (!isLoadingGenericState.value && genericAvailableActions.value.length === 0) {
    return true;
  }
  return false;
});

const executeGenericForward = async () => {
  if (!genericSignPin.value) {
    genericSignPinError.value = "Please enter your 4-digit signature PIN.";
    return;
  }
  if (!primaryGenericForwardAction.value) {
    genericSignPinError.value = "No forward transition available.";
    return;
  }

  isProcessingGenericSign.value = true;
  genericSignPinError.value = "";

  try {
    // 1. Verify digital signature PIN
    const pinRes = await axios.post("/dashboard/verify-signature-pin", {
      pin: genericSignPin.value
    });
    if (!pinRes.data?.valid) {
      genericSignPinError.value = pinRes.data?.message || "Invalid digital signature PIN.";
      isProcessingGenericSign.value = false;
      return;
    }

    // 2. Execute transition
    const formData = new FormData();
    formData.append("task_id", props.task.id);
    formData.append("transition_ids[]", primaryGenericForwardAction.value.id);
    formData.append("comments", genericSignRemark.value || "Submitted for statutory review.");
    formData.append("pin", genericSignPin.value);

    const res = await axios.post(`/dashboard/generic-workflow/tasks/${props.task.id}/execute`, formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    openGenericSignModal.value = false;
    genericSignPin.value = "";
    
    await Swal.fire({
      icon: "success",
      title: "Submitted Successfully",
      text: res.data?.message || `Application successfully forwarded to ${primaryGenericForwardAction.value.to_step?.name || 'Director'}.`,
      timer: 2500,
      showConfirmButton: false
    });

    emit("refresh");
    await fetchGenericState();
  } catch (err) {
    genericSignPinError.value = err.response?.data?.error || err.response?.data?.message || "Failed to execute workflow action.";
  } finally {
    isProcessingGenericSign.value = false;
  }
};

const executeGenericReturn = async () => {
  if (!genericReturnReason.value.trim()) return;
  if (!primaryGenericReturnAction.value) return;

  isProcessingGenericReturn.value = true;
  try {
    const formData = new FormData();
    formData.append("task_id", props.task.id);
    formData.append("transition_ids[]", primaryGenericReturnAction.value.id);
    formData.append("comments", genericReturnReason.value);

    await axios.post(`/dashboard/generic-workflow/tasks/${props.task.id}/execute`, formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    openGenericReturnModal.value = false;
    genericReturnReason.value = "";

    await Swal.fire({
      icon: "success",
      title: "Docket Returned",
      text: "The docket has been returned with directives.",
      timer: 2500,
      showConfirmButton: false
    });

    emit("refresh");
    await fetchGenericState();
  } catch (err) {
    alert(err.response?.data?.error || err.response?.data?.message || "Failed to return docket.");
  } finally {
    isProcessingGenericReturn.value = false;
  }
};

watch(() => [props.isOpen, props.task?.id], ([isOpen, taskId]) => {
  if (isOpen && taskId) {
    fetchGenericState();
  }
}, { immediate: true });

const currentUser = computed(() => page.props.user || {});


const userRoles = computed(() => {
  return page.props.user_roles || page.props.roles || [];
});

const isAgf = computed(() => {
  const r = (userRoles.value || []).map(x => String(x).toLowerCase());
  return r.includes('super user') || r.includes('accountant-general');
});

const isDirector = computed(() => {
  const r = (userRoles.value || []).map(x => String(x).toLowerCase());
  return r.includes('director');
});

const isDeputyDirector = computed(() => {
  const r = (userRoles.value || []).map(x => String(x).toLowerCase());
  return r.includes('deputy director');
});

const isAssistantDirector = computed(() => {
  const r = (userRoles.value || []).map(x => String(x).toLowerCase());
  return r.includes('assistant director');
});

const isUnitStaff = computed(() => {
  const r = (userRoles.value || []).map(x => String(x).toLowerCase());
  if (r.includes('regular user') || r.includes('regular users')) {
    return true;
  }
  const supervisorRoles = ['super user', 'director', 'deputy director', 'assistant director', 'super admin', 'admin'];
  return !r.some(role => supervisorRoles.includes(role));
});

const canAssign = computed(() => {
  return !isUnitStaff.value;
});

const isTaskActive = computed(() => {
  const s = (props.task?.status || '').toLowerCase();
  return s !== 'completed' && s !== 'archived';
});

// Check if assigned to current user
const isAssignedToMe = computed(() => {
  if (props.task?.assigned_to_me) return true;
  if (props.task?.assignments && Array.isArray(props.task.assignments)) {
    return props.task.assignments.some(a => a.assigned_to === currentUser.value?.id || a.assigned_to?.id === currentUser.value?.id);
  }
  return false;
});

// Check if user has an upline supervisor that assigned the task to them
const hasUpline = computed(() => {
  return !!props.task?.assigned_to_me || (props.task?.assignments && props.task.assignments.length > 0);
});

// Subordinate review check: true if any assignment under this user is in 'under review' status
const isSubordinateUnderReview = computed(() => {
  const taskStatus = (props.task?.status || '').toLowerCase();
  if (isAgf.value && taskStatus === 'under review') {
    return true;
  }
  if (!props.task?.assignments) return false;
  return props.task.assignments.some(a => {
    const isAssigner = a.assigned_by?.id === currentUser.value?.id || a.assigned_by === currentUser.value?.id;
    return isAssigner && (a.status === 'under review' || a.status === 'review');
  });
});

// Dynamic review approval label based on hierarchy tier
const approveButtonLabel = computed(() => {
  if (isAgf.value) return 'Final Executive Approval';
  if (isDirector.value) return 'Approve & Submit to AGF';
  if (isDeputyDirector.value) return 'Approve & Submit to Director';
  if (isAssistantDirector.value) return 'Approve & Submit to Deputy Director';
  return 'Approve & Submit Upward';
});

// Submissions collection
const submissionList = computed(() => {
  const list = [];
  if (!props.task) return list;

  if (props.task.assignments && Array.isArray(props.task.assignments)) {
    for (const a of props.task.assignments) {
      if (a.task_submissions && Array.isArray(a.task_submissions)) {
        for (const sub of a.task_submissions) {
          list.push({
            ...sub,
            user: a.assigned_to
          });
        }
      }
    }
  }

  if (props.task.assigned_to_me?.task_submissions && Array.isArray(props.task.assigned_to_me.task_submissions)) {
    for (const sub of props.task.assigned_to_me.task_submissions) {
      if (!list.some(s => s.id === sub.id)) {
        list.push({
          ...sub,
          user: currentUser.value
        });
      }
    }
  }

  return list;
});

const submissionCount = computed(() => submissionList.value.length);
const hasSubmissions = computed(() => submissionCount.value > 0);

// Total comments count across discussions
const commentsCount = computed(() => {
  if (props.task?.comments_count) return props.task.comments_count;
  let count = 0;
  if (props.task?.assigned_to_me?.comments) {
    count += props.task.assigned_to_me.comments.length;
  }
  if (props.task?.assignments) {
    for (const a of props.task.assignments) {
      if (a.comments) count += a.comments.length;
    }
  }
  return count;
});

// Deliverable URL helper
const getDeliverableUrl = (file) => {
  if (!file) return null;
  const path = file.path || file.file_path;
  if (!path) return null;
  if (path.startsWith('http')) return path;
  const base = page.props.base || '';
  return base + (path.startsWith('/') ? path : '/' + path);
};

const closeDrawer = () => {
  emit("close");
};

// Reset tab & index on task change
watch(() => props.task?.id, () => {
  selectedDocIndex.value = 0;
});

// Aggregate all documents attached to this task
const allDocs = computed(() => {
  if (!props.task) return [];
  const docs = [];
  if (props.task.taskdocs && Array.isArray(props.task.taskdocs)) {
    docs.push(...props.task.taskdocs);
  }
  if (props.task.file && props.task.file.path) {
    docs.push({
      file_name: props.task.file.name || 'Registry Document',
      path: props.task.file.path,
      size: props.task.file.size || 'Registry File'
    });
  }
  return docs;
});

const activeDoc = computed(() => {
  if (allDocs.value.length === 0) return null;
  return allDocs.value[selectedDocIndex.value] || allDocs.value[0];
});

const docUrl = computed(() => {
  if (!activeDoc.value || !activeDoc.value.path) return null;
  const path = activeDoc.value.path;
  if (path.startsWith('http')) return path;
  const base = page.props.base || '';
  return base + (path.startsWith('/') ? path : '/' + path);
});

const hasAssignments = computed(() => {
  const assignments = props.task?.assigned_out_assignments || props.task?.assignments || [];
  return assignments && assignments.length > 0;
});

const canReassign = computed(() => {
  const assignments = props.task?.assigned_out_assignments || props.task?.assignments || [];
  if (!assignments || assignments.length === 0) return true;
  const latest = assignments[0];
  return (latest.status || '').toLowerCase() === 'completed';
});

const reassignTitle = computed(() => {
  if (!canReassign.value) {
    return 'Task must be approved before reassigning';
  }
  return hasAssignments.value ? 'Reassign Task' : 'Assign Task';
});

const isPdf = computed(() => {
  if (!docUrl.value) return false;
  return docUrl.value.toLowerCase().includes('.pdf');
});

const isImage = computed(() => {
  if (!docUrl.value) return false;
  const url = docUrl.value.toLowerCase();
  return url.includes('.png') || url.includes('.jpg') || url.includes('.jpeg') || url.includes('.webp');
});

// Minutes list
const minutesList = computed(() => {
  if (!props.task || !props.task.minutes) return [];
  return props.task.minutes;
});

// Smart Suggested Routing
const suggestedRouting = computed(() => {
  return props.task?.suggested_routing || null;
});

// Priority
const priorityLabel = computed(() => {
  const p = props.task?.priority;
  if (p == 1) return 'High';
  if (p == 2) return 'Medium';
  if (p == 3) return 'Low';
  return 'Standard';
});

const priorityBadgeClass = computed(() => {
  const p = props.task?.priority;
  if (p == 1) return 'bg-red-50 text-red-700 border border-red-200';
  if (p == 2) return 'bg-orange-50 text-orange-700 border border-orange-200';
  if (p == 3) return 'bg-blue-50 text-blue-700 border border-blue-200';
  return 'bg-gray-100 text-gray-700';
});

// SLA Countdown calculation
const slaStatus = computed(() => {
  if (!props.task?.due_date) return 'none';
  if (props.task?.status?.toLowerCase() === 'completed') return 'completed';
  
  const due = new Date(props.task.due_date);
  due.setHours(23, 59, 59, 999);
  const now = new Date();
  const diffMs = due - now;
  const diffHours = Math.round(diffMs / (1000 * 60 * 60));
  const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

  if (diffMs < 0) {
    const overdueDays = Math.abs(diffDays);
    return {
      type: 'overdue',
      label: overdueDays === 0 ? 'Overdue Today' : `Overdue (${overdueDays}d ago)`,
    };
  }
  if (diffHours <= 24) {
    return {
      type: 'urgent',
      label: `Due in ${Math.max(1, diffHours)}h`,
    };
  }
  if (diffDays <= 3) {
    return {
      type: 'soon',
      label: `Due in ${diffDays}d`,
    };
  }
  return {
    type: 'ok',
    label: `Due ${formatDate(props.task.due_date)}`,
  };
});

const slaLabel = computed(() => {
  return slaStatus.value?.label || 'No Deadline';
});

const slaBadgeClass = computed(() => {
  const type = slaStatus.value?.type;
  if (type === 'overdue') return 'bg-red-50 text-red-700 border border-red-200 animate-pulse';
  if (type === 'urgent') return 'bg-amber-50 text-amber-800 border border-amber-200';
  if (type === 'soon') return 'bg-blue-50 text-blue-700 border border-blue-200';
  if (type === 'completed') return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
  return 'bg-gray-50 text-gray-600 border border-gray-200';
});

const slaDotClass = computed(() => {
  const type = slaStatus.value?.type;
  if (type === 'overdue') return 'bg-red-600';
  if (type === 'urgent') return 'bg-amber-500';
  if (type === 'soon') return 'bg-blue-500';
  if (type === 'completed') return 'bg-emerald-500';
  return 'bg-gray-400';
});

// Formatters
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

// Keyboard navigation (J / K / Esc)
const handleKeyDown = (e) => {
  if (!props.isOpen) return;
  if (e.key === 'Escape') {
    closeDrawer();
  } else if ((e.key === 'j' || e.key === 'ArrowDown') && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
    emit('next');
  } else if ((e.key === 'k' || e.key === 'ArrowUp') && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
    emit('prev');
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.drawer-fade-enter-active,
.drawer-fade-leave-active {
  transition: opacity 0.25s ease;
}
.drawer-fade-enter-from,
.drawer-fade-leave-to {
  opacity: 0;
}

.drawer-slide-enter-active,
.drawer-slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-slide-enter-from,
.drawer-slide-leave-to {
  transform: translateX(100%);
}
</style>
