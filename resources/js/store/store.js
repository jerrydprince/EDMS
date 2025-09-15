
  import { createStore } from 'vuex'
  import axios from "axios"

  // Create a new store instance.
  const store = createStore({
    state () {
      return {
        notifications: [],
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
      getTotalTaskNotifications(state){
        return state.total_task_notifications
      },
      getNewChat(state){
        return state.new_chat;
      }
    },

    mutations: {
      setNotifications (state, nots) {
        //state.notifications.push(nots)

        // // //state.notifications.push({task: nots})

       // state.notifications.splice(0,1, nots)
       state.notifications = Array.isArray(nots) ? nots : [nots]

        // if (Array.isArray(nots)) {
        //   state.notifications = nots; // Replace the notifications array with the new one
        // } else {
        //   state.notifications.push(nots); // Add a single notification if `nots` is not an array
        // }
      },

      addNotification(state, not) {
      // Insert new notification at the top
      state.notifications.unshift(not)
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
          console.log('this is from the store ', response)
          // notifications.value = response.data.notifications
          // total_task_notifications.value = response.data.count
          //if(response.data.notifications && response.data.notifications.length > 0){
            commit('setNotifications', response.data.notifications)
           // commit('addNotification', response.data.notifications)
          //}
          
          commit("setTotalTaskNotifications", response.data.count)
        })
      }
    }
  })

  export default store;