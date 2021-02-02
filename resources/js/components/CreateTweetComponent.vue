<template>
  <div class="container">
    <div class="row">
      <div class="col" v-if="!loader">
        <div class="form-group">
          <label class="font-weight-bold" for="tweet">Tweet</label>
          <textarea class="form-control" id="tweet" v-model="tweet" @keydown="onKeyDown"></textarea>
          <span>Characters left: {{ keysLeft }}</span>
        </div>
        <div class="form-group">
          <label class="font-weight-bold" for="media">Media</label>
          <input accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm" type="file" class="form-control-file" id="media" ref="media">
        </div>
        <div v-if="error" class="alert alert-danger text-small text-center">{{ error }}</div>
        <button class="btn btn-primary btn-block" type="submit" @click="post">Tweet</button>
      </div>
      <div class="col alert alert-primary text-center medium-text" role="alert" v-if="loader">Uploading your tweet, please wait</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      media: null,
      tweet: '',
      tweetMaxLength: 280,
      error: '',
      loader: false
    }
  },
  methods: {
    onKeyDown(evt) {
      if (this.tweet.length >= this.tweetMaxLength) {
        if (evt.keyCode >= 48 && evt.keyCode <= 90) {
          evt.preventDefault()
          return
        }
      }
    },
    post() {
      this.error = ''
      if (this.tweet.length === 0) {
        this.error = 'Please write your tweet.'
      }
      let formData = new FormData();
      formData.append('tweet', this.tweet);
      if(this.$refs.media.files.length) {
        this.media = this.$refs.media.files[0];
        formData.append('media', this.media);
      }
      this.loader = true
      axios.post('create-tweet',
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      ).then(() => {
        this.loader = false
        this.tweet = ''
        this.media = null
      })
          .catch(() => {
            this.loader = false
            this.tweet = ''
            this.media = null
            this.error = 'There was an error posting your tweet.'
          });
    }
  },
  computed: {
    keysLeft() {
      return this.tweetMaxLength - this.tweet.length
    }
  }
}
</script>
