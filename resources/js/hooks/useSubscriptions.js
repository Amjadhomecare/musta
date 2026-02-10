// resources/js/hooks/useSubscriptions.js
import { ref, computed } from 'vue';
import useSWRV from 'swrv';
import axios from 'axios';

export function useSubscriptions() {
  const limit = ref(10);
  const nextCursor = ref(null);
  const prevCursors = ref([]);
  const hasMore = ref(false);  

  const fetchKey = computed(() => {
    let url = `/stripe-subscriptions?limit=${limit.value}`;
    if (nextCursor.value) {
      url += `&starting_after=${nextCursor.value}`;
    } else if (prevCursors.value.length > 0) {
      url += `&ending_before=${prevCursors.value[prevCursors.value.length - 1]}`;
    }
    return url;
  });

  const { data, error, isValidating } = useSWRV(fetchKey, async (url) => {
    const response = await axios.get(url);
    hasMore.value = response.data.hasMore;
    return response.data;
  });

  const nextPage = () => {
    if (data.value?.data?.length) {
      prevCursors.value.push(data.value.data[0].id); 
      const lastSubscription = data.value.data[data.value.data.length - 1];
      nextCursor.value = lastSubscription.id;
    }
  };

  const previousPage = () => {
    if (prevCursors.value.length > 0) {
      nextCursor.value = null; 
      prevCursors.value.pop();
    }
  };

  return {
    subscriptions: computed(() => data.value?.data || []),
    isLoading: computed(() => isValidating.value),
    hasMore,
    nextPage,
    previousPage,
    prevCursors: computed(() => prevCursors.value),
    data  
  };
}
