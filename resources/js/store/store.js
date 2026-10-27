
  import { createStore } from 'vuex'
  import axios from "axios"

  // Create a new store instance.
  const store = createStore({
    state () {
      return {
        notifications: [],
        all_notifications: [],
        total_task_notifications: 0,
        new_chat: [],
      }
    },

    getters:{
      getNotifications(state){
        if(state.notifications && state.notifications.length > 0){
          return state.notifications
        }else{
          return []
        }
      },
      getAllNotifications(state){
        return state.all_notifications || [];
      },
      getTotalTaskNotifications(state){
        return state.total_task_notifications
      },
      getNewChat(state){
        return state.new_chat;
      }
    },

    mutations: {
      setNotifications (state, nots) {
        state.notifications = Array.isArray(nots) ? nots : [nots]
      },

      setAllNotifications(state, nots) {
        state.all_notifications = nots || [];
      },

      addNotification(state, not) {
        state.all_notifications.unshift(not);
        state.notifications.unshift(not);
        state.total_task_notifications++;
      },

      setTotalTaskNotifications(state, counter){
        state.total_task_notifications = counter
      },

      setNewChat(state, data){
        state.new_chat.push(data)
      }
    },
    
    actions: {
      async fetchNotifications({commit}){
        await axios.get('/user-notifications').then((response)=>{
          commit('setNotifications', response.data.notifications)
          commit('setAllNotifications', response.data.all_notifications || [])
          commit("setTotalTaskNotifications", response.data.count)
        }).catch(err => {
          console.error("Failed to fetch notifications:", err);
        })
      }
    }
  })

  export default store;