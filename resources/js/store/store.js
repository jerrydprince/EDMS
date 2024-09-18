// import { reactive, ref, onMounted } from "vue"
// import axios from 'axios';

// const notifications = ref()
// const total_task_notifications = ref();
// const getUserNotifications = ()=>{
//     axios.get('/user-notifications').then((response)=>{
//       notifications.value = response.data.notifications
//       total_task_notifications.value = response.data.count
//     })
//   }

//   export const store = reactive({
//     notifications: getUserNotifications(),
//     total_task_notifications: total_task_notifications.value
// })

//   onMounted(()=>{
//     getUserNotifications()
//   })

  import { createStore } from 'vuex'
  import axios from "axios"

  // Create a new store instance.
  const store = createStore({
    state () {
      return {
        notifications: [],
        total_task_notifications: 0,
        new_chat: 0,
      }
    },
    getters:{
      getNotifications(state){
        return state.notifications
      },
      getTotalTaskNotifications(state){
        return state.total_task_notifications
      },
      getNewChat(state){
        return state.new_chat;
      }
    },
    mutations: {
      setNotifications (state, notifications) {
        state.notifications = notifications
      },

      setTotalTaskNotifications(state, counter){
        state.total_task_notifications = counter
      },

      setNewChat(state, chat){
        state.new_chat = chat
      }
    },
    actions: {
      fetchNotifications({commit}){
        axios.get('/user-notifications').then((response)=>{
          // notifications.value = response.data.notifications
          // total_task_notifications.value = response.data.count
          commit('setNotifications', response.data.notifications)
          commit("setTotalTaskNotifications", response.data.count)
        })
      }
    }
  })

  export default store;