<template>
  <div class="review-card">
    <div class="review-card__header">
      <div class="review-card__avatar">
        <img v-if="review.avatarUrl" :src="review.avatarUrl" :alt="review.author" />
        <span v-else class="review-card__avatar-initials">{{ initials }}</span>
      </div>
      <div class="review-card__author-info">
        <span class="review-card__author-name">{{ review.author }}</span>
        <span class="review-card__date">{{ formattedDate }}</span>
      </div>
      <div class="review-card__rating">
        <span class="review-card__rating-value">{{ Number(review.rating).toFixed(1) }}</span>
        <svg class="review-card__star-icon" viewBox="0 0 16 16" fill="currentColor">
          <path
            d="M8 1l1.85 3.75L14 5.5l-3 2.92.71 4.12L8 10.5l-3.71 1.95.71-4.12-3-2.92 4.15-.75z"
          />
        </svg>
      </div>
    </div>

    <div class="review-card__stars">
      <svg
        v-for="i in 5"
        :key="i"
        class="review-card__star"
        :class="{ 'review-card__star--filled': i <= Math.round(review.rating) }"
        viewBox="0 0 16 16"
        fill="currentColor"
      >
        <path
          d="M8 1l1.85 3.75L14 5.5l-3 2.92.71 4.12L8 10.5l-3.71 1.95.71-4.12-3-2.92 4.15-.75z"
        />
      </svg>
    </div>

    <p class="review-card__text" :class="{ 'review-card__text--truncated': !expanded && isLong }">
      {{ review.text }}
    </p>

    <button v-if="isLong" class="review-card__toggle" @click="expanded = !expanded">
      {{ expanded ? 'Ver menos' : 'Ver mais' }}
    </button>

    <div v-if="review.propertyName" class="review-card__property">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M8 1.5L1.5 7v7h4v-4h5v4h4V7z" />
      </svg>
      <span>{{ review.propertyName }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReviewCard',

  props: {
    review: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      expanded: false,
      maxLength: 160,
    }
  },

  computed: {
    initials() {
      if (!this.review.author) return '?'
      return this.review.author
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
    },

    formattedDate() {
      if (!this.review.date) return ''
      const date = new Date(this.review.date)
      return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
    },

    isLong() {
      return (this.review.text ?? '').length > this.maxLength
    },
  },
}
</script>

<style scoped>
.review-card {
  background: var(--color-bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: box-shadow 0.2s ease;
  width: 100%;
  box-sizing: border-box;
  color: var(--color-primary-text);
}

.review-card:hover {
  box-shadow: var(--shadow-hover-blue);
}

.review-card__header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.review-card__avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: #e8eaf6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.review-card__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.review-card__avatar-initials {
  font-size: 15px;
  font-weight: 600;
  color: #1a2fa8;
  font-family: inherit;
}

.review-card__author-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.review-card__author-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-black-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.review-card__date {
  font-size: 12px;
  color: #9ca3af;
}

.review-card__rating {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #f0f2ff;
  border-radius: 20px;
  padding: 4px 10px;
  flex-shrink: 0;
}

.review-card__rating-value {
  font-size: 13px;
  font-weight: 700;
  color: #1a2fa8;
}

.review-card__star-icon {
  width: 12px;
  height: 12px;
  color: #1a2fa8;
}

.review-card__stars {
  display: flex;
  gap: 3px;
}

.review-card__star {
  width: 15px;
  height: 15px;
  color: #d1d5db;
}

.review-card__star--filled {
  color: #f59e0b;
}

.review-card__text {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--color-black-text);
  margin: 0;
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
}

.review-card__text--truncated {
  -webkit-line-clamp: 4;
}

.review-card__toggle {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  color: #1a2fa8;
  padding: 0;
  text-align: left;
  align-self: flex-start;
}

.review-card__toggle:hover {
  text-decoration: underline;
}

.review-card__property {
  display: flex;
  align-items: center;
  gap: 6px;
  padding-top: 10px;
  border-top: 1px solid #f3f4f6;
  margin-top: 2px;
}

.review-card__property svg {
  width: 14px;
  height: 14px;
  color: #9ca3af;
  flex-shrink: 0;
}

.review-card__property span {
  font-size: 12px;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
